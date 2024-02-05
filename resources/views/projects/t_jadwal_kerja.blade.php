@if(!$req->has('id'))
<div class="bg-white p-3 rounded-xl h-[570px]">
  <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions">
    <template #header>
      <RouterLink v-if="currentMenu?.can_create||true||store.user.data.username==='developer'" :to="$route.path+'/create?'+(Date.parse(new Date()))" class="bg-green-500 text-white hover:bg-green-600 rounded-[6px] py-2 px-[12.5px]">
        <icon fa="plus" />
        Tambah Data
      </RouterLink>
    </template>
  </TableApi>
</div>
@else

@verbatim
<div class="flex flex-col gap-y-3">
  <div class="flex gap-x-4 px-2">
    <div class="flex flex-col border rounded shadow-sm px-6 py-6 <md:w-full w-full bg-white">
      <div class="mb-4">
        <h1 class="text-[24px] mb-4 font-bold">
          Jadwal Kerja   
        </h1>
        <hr>
      </div>
      
      <div class="grid <md:grid-cols-1 grid-cols-2 gap-2">
        <!-- START COLUMN -->
        <div>
          <label class="font-semibold">Nomor<span class="text-red-500 space-x-0 pl-0"></span></label>
          <FieldX :bind="{ readonly: true }" 
              class="col-span-12 !mt-0 w-full"
              :value="values.nomor" :errorText="formErrors.nomor?'failed':''"
              @input="v=>values.nomor=v" :hints="formErrors.nomor" 
              :check="false"
              label=""
          />
        </div>
        <div>
          <label class="font-semibold">Tipe Jam Kerja<span class="text-red-500 space-x-0 pl-0"></span></label>
            <FieldSelect
              :bind="{ disabled: !actionText, clearable:false }" 
              class="col-span-12 !mt-0 w-full"
              :value="values.tipe_jam_kerja_id" 
              @input="v=>values.tipe_jam_kerja_id=v"
              @update:valueFull="(v)=>{
                values.tipe_jam_kerja = v.value
                generate()
              }"
              :errorText="formErrors.tipe_jam_kerja_id?'failed':''" 
              :hints="formErrors.tipe_jam_kerja_id"
              valueField="id" displayField="value"
              placeholder="Pilih Tipe Jam"
              label=""
              :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest:true,
                      transform:false,
                      where:`this.group='TIPEJAM' AND this.is_active='true'`,
                      join:true, 
                      selectfield: 'this.id, this.code, this.value, this.is_active'
                    }
                }"
              :check="false"
            />
        </div>
       
        <div>
          <label class="col-span-12">Keterangan<label class="text-red-500 space-x-0 pl-0">*</label></label>
           <FieldX :bind="{ readonly: !actionText }" 
              type="textarea" 
              :value="values.keterangan"
              class="col-span-12 !mt-0 w-full"
              @input="v=>values.keterangan=v" 
              :check="false"
              />        
        </div>
       
        <div >
           <label class="col-span-12">Status<label class="text-red-500 space-x-0 pl-0"></label></label>
            <FieldX :bind="{ readonly: true }" 
              type="text" 
              :value="values.status"
              class="col-span-12 !mt-0 w-full"
              @input="v=>values.status=v" 
              :check="false"
              /> 
        </div>
        <!-- END COLUMN -->
      </div>
      <div class="col-span-8 md:col-span-12 mt-5">
      <hr>
        <div class="flex mt-2 justify-end">
          <h3 class="font-semibold text-right">Detail Hari</h4><br>
          <div class="content-end flex">
            <button  v-show="isRead && values.tipe_jam_kerja != 'OFFICE'" @click="move" type="button" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold ml-2 px-2 py-1 rounded flex items-center justify-center">
              <icon fa="tools" size="sm mr-0.5"/> Sesuaikan Jadwal Karyawan
            </button>
            <button v-if="actionText" @click="generate(true)" type="button" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold ml-2 px-2 py-1 rounded flex items-center justify-cente">
                <icon fa="bolt" size="sm mr-0.5"/> Generate
            </button>
          </div>
        </div>
        <table class="w-full overflow-x-auto mt-2">
          <thead>
            <tr class="border-y">
              <td class="text-black font-bold text-capitalize px-2 text-left w-[5%]">No.</td>
              <td class="text-black font-bold text-capitalize px-2 text-left w-[10%]">Hari</td>
              <td class="text-black font-bold text-capitalize px-2 text-left w-[15%]">Tipe Hari <label class="text-red-500 space-x-0 pl-0">*</label></td>
              <td class="text-black font-bold text-capitalize px-2 text-left w-[15%]">Jam Kerja <label class="text-red-500 space-x-0 pl-0">*</label></td>
              <td class="text-black font-bold text-capitalize px-2 text-left w-[10%]">Waktu Mulai <label class="text-red-500 space-x-0 pl-0">*</label></td>
              <td class="text-black font-bold text-capitalize px-2 text-left w-[10%]">Waktu Akhir <label class="text-red-500 space-x-0 pl-0">*</label></td>
            </tr>
          </thead>
          <tbody>
            <tr v-for="a,i in trx_dtl.items" :key="a._id">
              <td class="text-center px-1 border border-gray-200">{{ i+1 }}</td>
              <td class="text-center px-1 border border-gray-200">{{ a.day }}</td>
              <td class="text-center px-1 border border-gray-200">
                  <FieldSelect
                  :bind="{ disabled: !actionText || values.tipe_jam_kerja?.toLowerCase() == 'office' ? true : false, clearable:false }" class="!mt-0 w-full"
                  :value="trx_dtl.items[i].tipe_hari" @input="v=>trx_dtl.items[i].tipe_hari=v"
                  label="" placeholder="Pilih Faktor"
                  :options="['KERJA','LIBUR']"
                  valueField="key" displayField="key" :check="false"
                  />
              </td>
              <td class="text-center px-1 border border-gray-200">
                <FieldSelect
                  class="w-full !mt-0"
                  :bind="{ disabled: !actionText, clearable:false }"
                  :value="trx_dtl.items[i].m_jam_kerja_id" 
                  :check="false"
                  @input="(v)=>{
                    trx_dtl.items[i].m_jam_kerja_id=v
                  }"
                  @update:valueFull="(v)=>{
                    log(v)
                    trx_dtl.items[i].waktu_mulai=v.waktu_mulai
                    trx_dtl.items[i].waktu_akhir=v.waktu_akhir
                  }"
                  displayField="kode"
                  valueField="id"
                  :api="{
                      url: `${store.server.url_backend}/operation/m_jam_kerja`,
                      headers: {
                        'Content-Type': 'Application/json',
                        Authorization: `${store.user.token_type} ${store.user.token}`
                      },
                      params: {
                        selectfield: 'waktu_mulai,waktu_akhir,this.id,this.kode',
                        where:`this.is_active='true' and this.tipe_jam_kerja_id=${values.tipe_jam_kerja_id ?? 0}`,
                        transform:false,
                      }
                  }"
                  fa-icon="search" :check="true" />
              </td>
              <td class="text-center px-1 border border-gray-200">   
                <FieldX :bind="{ readonly: !actionText }" class="w-full py-2 !mt-0"
                    :value="trx_dtl.items[i].waktu_mulai ?? ''" 
                    @input="v=>trx_dtl.items[i].waktu_mulai=v" 
                    :check="false"
                    type="text"
                    label=""
                    placeholder=""
                />
              </td>
              <td class="text-center px-1 border border-gray-200">   
                <FieldX :bind="{ readonly: !actionText }" class="w-full py-2 !mt-0"
                    :value="trx_dtl.items[i].waktu_akhir ?? ''" 
                    @input="v=>trx_dtl.items[i].waktu_akhir=v" 
                    :check="false"
                    type="text"
                    label=""
                    placeholder=""
                />
              </td>
            </tr>
          </tbody>
        </table>
        <!-- ACTION BUTTON START -->
        <div class="flex flex-row justify-end space-x-[20px] mt-[5em]">
          <button @click="onBack" class="bg-[#EF4444] hover:bg-[#ed3232] text-white px-[36.5px] py-[12px] rounded-[6px] ">
            Kembali
          </button>
          <button v-show="actionText" @click="onSave" class="bg-[#10B981] hover:bg-[#0ea774] text-white px-[36.5px] py-[12px] rounded-[6px] ">
            Simpan
          </button>
        </div>
    </div>
  </div>
</div>
@endverbatim
@endif