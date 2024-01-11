@if(!$req->has('id'))
<div class="bg-white p-6 rounded-xl h-[570px]">
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
      <!-- HEADER START -->
      <div class="flex flex-col items-start mb-2 pb-4">
        <h1 class="text-[24px] mb-[15px] font-bold">
         Form Standart Gaji
        </h1>
      </div>
      <!-- HEADER END -->
      <!-- FORM START -->
      <div class="grid grid-cols-8 md:grid-cols-12 text-[14px] gap-x-[29px] gap-y-[26px]">
        <!-- <div class="col-span-8 md:col-span-6">
          <div class="gap-y-2">
            <label class="col-span-12">Direktorat<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX  
              class="w-full py-2 !mt-0"
              :bind="{ disabled:true, readonly:true }"
              :value="values.direktorat" :errorText="formErrors.direktorat?'failed':''"
              @input="v=>values.direktorat=v"
              :hints="formErrors.direktorat"
              :check="false" />
                     
          </div>
        </div> -->

        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">Kode<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: !actionText }" 
              :value="values.kode" :errorText="formErrors.kode?'failed':''"
              @input="v=>values.kode=v" :hints="formErrors.kode" 
              :check="false"
              class="col-span-12 !mt-0 w-full"
              
            />
            
                     
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">Grading<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldSelect
              :bind="{ disabled: !actionText, clearable:false }"
              class="col-span-12 !mt-0 w-full"
              :value="values.grading_id"
              @input="(v) => {
                values.grading_id = v;
                updateTunjanganKemahalan(); // Memanggil fungsi untuk memperbarui nilai tunjangan_kemahalan_id
              }"
              :errorText="formErrors.grading_id?'failed':''"
              displayField="value" 
              @update:valueFull="(v)=>{
                if(values.kode?.split(' ').length > 1){
                  values.kode = `${v.code} ${values.kode?.split(' ')[1]} ${values.kode?.split(' ')[2]}`
                }else{                
                  values.kode=v.code
                }
              }"
              :hints="formErrors.grading_id"
                :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                    params: {
                      where: `this.group = 'GRADING'`,
                      selectfield:'this.id,this.value,this.code'
                    }
              }"
              valueField="id"
              :check="false"
              label=""
              placeholder="Pilih Grading" 
            />            
          </div>
        </div>

         <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">Zona<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldSelect
              :bind="{ disabled: !actionText, clearable:false }"
              class="col-span-12 !mt-0 w-full"
              :value="values.m_zona_id"
              @input="(v) => {
                values.m_zona_id = v;
                updateTunjanganKemahalan(); // Memanggil fungsi untuk memperbarui nilai tunjangan_kemahalan_id
              }"
              :errorText="formErrors.m_zona_id?'failed':''" 
              :hints="formErrors.m_zona_id"
              displayField="nama"
              @update:valueFull="(objVal)=>{
                if(values.kode?.split(' ').length === 1){                
                  values.kode+=` ${objVal.nama}`
                }else if(values.kode?.split(' ').length > 1){
                  values.kode = `${values.kode?.split(' ')[0]} ${objVal.nama}`
                }
              }"
              :api="{
                    url: `${store.server.url_backend}/operation/m_zona`,
                    headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                    params: {
                      join:true, 
                      single: true,
                      selectfield: 'this.id,this.nama,this.kode'
                    }
              }"
              valueField="id"
              :check="false"
              label=""
              placeholder="Pilih Zona" 
            />            
          </div>
        </div>

        
       <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">Posisi<label class="text-red-500 space-x-0 pl-0"></label></label>
            <FieldSelect
              :bind="{ disabled: !actionText, clearable:false }" class="col-span-12 !mt-0 w-full"
              :value="values.m_posisi_id" @input="v=>values.m_posisi_id=v"
              :errorText="formErrors.m_posisi_id?'failed':''" 
              :hints="formErrors.m_posisi_id"
              displayField="desc_kerja"
              :api="{
                    url: `${store.server.url_backend}/operation/m_posisi`,
                    headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              }"
              valueField="id"
              :check="false"
              label=""
              placeholder="Pilih Posisi" 
            />            
          </div>
        </div>

        <div class="col-span-8 md:col-span-6">
          <div class="flex items-center gap-2">
            <div class="flex flex-col w-[161px] items-start gap-y-2">
              <label class="col-span-12">Gaji Pokok<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldSelect
                  :bind="{ disabled: !actionText, clearable:false }" class="col-span-12 !mt-0 w-full"
                  :value="values.gaji_pokok_periode || 'Bulanan'" @input="v=>values.gaji_pokok_periode=v"
                  :errorText="formErrors.gaji_pokok_periode?'failed':''" 
                  :hints="formErrors.gaji_pokok_periode"
                  valueField="id" displayField="key"
                  :options="['Bulanan']"
                  :check="false"
                  label=""
                  placeholder="Pilih Periode" 
                 />            
            </div>
            <div class="flex flex-col flex-1 ">
              <FieldNumber
                :bind="{ readonly: !actionText }"
                :value="values.gaji_pokok" @input="(v)=>values.gaji_pokok=v"
                :errorText="formErrors.gaji_pokok?'failed':''" 
                :hints="formErrors.gaji_pokok"
                :check="false"
                class="col-span-12 !mt-5 w-full"
                label=""
                placeholder="0" 
              />         
            </div>
          </div>
        </div>




        <div class="col-span-8 md:col-span-6">
          <div class="flex items-center gap-2">
            <div class="flex flex-col w-[161px] items-start gap-y-2">
              <label class="col-span-12">Uang Saku<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldSelect
                  :bind="{ disabled: !actionText, clearable:false }" class="col-span-12 !mt-0 w-full"
                  :value="values.uang_saku_periode || 'Bulanan'" @input="v=>values.uang_saku_periode=v"
                  :errorText="formErrors.uang_saku_periode?'failed':''" 
                  :hints="formErrors.uang_saku_periode"
                  valueField="id" displayField="key"
                  :options="['Bulanan']"
                  :check="false"
                  label=""
                  placeholder="Pilih Periode" 
                 />            
            </div>
            <div class="flex flex-col flex-1 ">
               <FieldNumber
                :bind="{ readonly: !actionText }"
                :value="values.uang_saku" @input="(v)=>values.uang_saku=v"
                :errorText="formErrors.uang_saku?'failed':''" 
                :hints="formErrors.uang_saku"
                :check="false"
                class="col-span-12 !mt-5 w-full"
                label=""
                placeholder="0" 
              />            
            </div>
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="flex items-center gap-2">
            <div class="flex flex-col w-[161px] items-start gap-y-2">
              <label class="col-span-12">Tunjangan Posisi<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldSelect
                  :bind="{ disabled: !actionText, clearable:false }" class="col-span-12 !mt-0 w-full"
                  :value="values.tunjangan_posisi_periode || 'Bulanan'" @input="v=>values.tunjangan_posisi_periode=v"
                  :errorText="formErrors.tunjangan_posisi_periode?'failed':''" 
                  :hints="formErrors.tunjangan_posisi_periode"
                  valueField="id" displayField="key"
                  :options="['Bulanan']"
                  :check="false"
                  label=""
                  placeholder="Pilih Periode" 
                 />            
            </div>
            <div class="flex flex-col flex-1 ">
                 <FieldNumber
                :bind="{ readonly: !actionText }"
                :value="values.tunjangan_posisi" @input="(v)=>values.tunjangan_posisi=v"
                :errorText="formErrors.tunjangan_posisi?'failed':''" 
                :hints="formErrors.tunjangan_posisi"
                :check="false"
                class="col-span-12 !mt-5 w-full"
                label=""
                placeholder="0" 
              />            
            </div>
          </div>
        </div>

                <div class="col-span-8 md:col-span-6">
          <div class="flex items-center gap-2">
            <div class="flex flex-col w-[161px] items-start gap-y-2">
              <label class="col-span-12">Tunjangan Kemahalan<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldSelect
                  :bind="{ disabled: !actionText, clearable:false }" class="col-span-12 !mt-0 w-full"
                  :value="values.tunjangan_kemahalan_periode || 'Bulanan'" @input="v=>values.tunjangan_kemahalan_periode=v"
                  :errorText="formErrors.tunjangan_kemahalan_periode?'failed':''" 
                  :hints="formErrors.tunjangan_kemahalan_periode"
                  valueField="id" displayField="key"
                  :options="['Bulanan']"
                  :check="false"
                  label=""
                  placeholder="Pilih Periode" 
                 />            
            </div>
            
            <div class="flex flex-col flex-1 ">
              <FieldPopup
              class="w-full py-2 mt-5"
                :bind="{ readonly: !actionText }"
                :value="values.tunjangan_kemahalan_id" @input="(v)=>values.tunjangan_kemahalan_id=v"
                :errorText="formErrors.tunjangan_kemahalan_id?'failed':''" 
                :hints="formErrors.tunjangan_kemahalan_id" 
                valueField="id" displayField="besaran"
                :api="{
                    url: `${store.server.url_backend}/operation/m_tunj_kemahalan`,
                    headers: {
                      //'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest:true,
                      single:true,
                      where:`this.is_active='true'`,
                      transform:false,
                      searchfield: 'this.kode,this.besaran,this.desc'
                    }
                }"
                placeholder="Pilih Tunjangan Kemahalan" label="" :check="false" 
                :columns="[{
                  headerName: 'No',
                  valueGetter:(p)=>p.node.rowIndex + 1,
                  width: 60,
                  sortable: false, resizable: false, filter: false,
                  cellClass: ['justify-center', 'bg-gray-50']
                },
                {
                  flex: 1,
                  field: 'kode',
                  sortable: false, resizable: true, filter: 'ColFilter',
                  cellClass: ['border-r', '!border-gray-200', 'justify-center']
                },
                {
                  flex: 1,
                  field: 'besaran',
                  sortable: false, resizable: true, filter: 'ColFilter',
                  cellClass: ['border-r', '!border-gray-200', 'justify-center']
                },
                {
                  flex: 1,
                  field: 'desc',
                  sortable: false, resizable: true, filter: 'ColFilter',
                  cellClass: ['border-r', '!border-gray-200', 'justify-center']
                },
                ]"
              />
              

            </div>
          </div>
        </div>


                <div class="col-span-8 md:col-span-6">
          <div class="flex items-center gap-2">
            <div class="flex flex-col w-[161px] items-start gap-y-2">
              <label class="col-span-12">Uang Makan<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldSelect
                  :bind="{ disabled: !actionText, clearable:false }" class="col-span-12 !mt-0 w-full"
                  :value="values.uang_makan_periode || 'Bulanan'" @input="v=>values.uang_makan_periode=v"
                  :errorText="formErrors.uang_makan_periode?'failed':''" 
                  :hints="formErrors.uang_makan_periode"
                  valueField="id" displayField="key"
                  :options="['Bulanan']"
                  :check="false"
                  label=""
                  placeholder="Pilih Periode" 
                 />            
            </div>
            <div class="flex flex-col flex-1 ">
                <FieldNumber
                :bind="{ readonly: !actionText }"
                :value="values.uang_makan" @input="(v)=>values.uang_makan=v"
                :errorText="formErrors.uang_makan?'failed':''" 
                :hints="formErrors.uang_makan"
                :check="false"
                class="col-span-12 !mt-5 w-full"
                label=""
                placeholder="0" 
              />                
            </div>
          </div>
        </div>


        <div class="col-span-8 md:col-span-6">
          <div class="flex items-center gap-2">
            <div class="flex flex-col w-[161px] items-start gap-y-2">
              <label class="col-span-12">Tunjangan Masa Kerja<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldSelect
                  :bind="{ disabled: !actionText, clearable:false }" class="col-span-12 !mt-0 w-full"
                  :value="values.tunjangan_tetap_periode || 'Bulanan'" @input="v=>values.tunjangan_tetap_periode=v"
                  :errorText="formErrors.tunjangan_tetap_periode?'failed':''" 
                  :hints="formErrors.tunjangan_tetap_periode"
                  valueField="id" displayField="key"
                  :options="['Bulanan']"
                  :check="false"
                  label=""
                  placeholder="Pilih Periode" 
                 />            
            </div>
            <div class="flex flex-col flex-1 ">
                 <FieldNumber
                :bind="{ readonly: !actionText }"
                :value="values.tunjangan_tetap" @input="(v)=>values.tunjangan_tetap=v"
                :errorText="formErrors.tunjangan_tetap?'failed':''" 
                :hints="formErrors.tunjangan_tetap"
                :check="false"
                class="col-span-12 !mt-5 w-full"
                label=""
                placeholder="0" 
              />              
            </div>
          </div>
        </div>



         <div class="col-span-8 md:col-span-6">
         <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">Deskripsi<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: !actionText }" label="" placeholder="Tuliskan Deskripsi" class="col-span-12 !mt-0 w-full"
              :value="values.desc" :errorText="formErrors.desc?'failed':''"
              @input="v=>values.desc=v" :hints="formErrors.desc_kerja" :check="false"
            />           
          </div>
        </div>



        <div class="flex flex-col gap-2">
          <label
            class="inline-block pl-[0.15rem] hover:cursor-pointer"
            for="is_active"
            >Status</label
          >
          <div class="flex w-40 items-center">
            <div class="flex-auto">
              <i class="text-red-500">InActive</i>
            </div>
            <div class="flex-auto">
              <input
                class="mr-2 mt-[0.3rem] h-3.5 w-8 appearance-none rounded-[0.4375rem] bg-neutral-300 before:pointer-events-none before:absolute before:h-3.5 before:w-3.5 before:rounded-full before:bg-transparent before:content-[''] after:absolute after:z-[2] after:-mt-[0.1875rem] after:h-5 after:w-5 after:rounded-full after:border-none after:bg-blue-500 after:shadow-[0_0px_3px_0_rgb(0_0_0_/_7%),_0_2px_2px_0_rgb(0_0_0_/_4%)] after:transition-[background-color_0.2s,transform_0.2s] after:content-[''] checked:bg-primary checked:after:absolute checked:after:z-[2] checked:after:-mt-[3px] checked:after:ml-[1.0625rem] checked:after:h-5 checked:after:w-5 checked:after:rounded-full checked:after:border-none checked:after:bg-primary checked:after:shadow-[0_3px_1px_-2px_rgba(0,0,0,0.2),_0_2px_2px_0_rgba(0,0,0,0.14),_0_1px_5px_0_rgba(0,0,0,0.12)] checked:after:transition-[background-color_0.2s,transform_0.2s] checked:after:content-[''] hover:cursor-pointer focus:outline-none focus:ring-0 focus:before:scale-100 focus:before:opacity-[0.12] focus:before:shadow-[3px_-1px_0px_13px_rgba(0,0,0,0.6)] focus:before:transition-[box-shadow_0.2s,transform_0.2s] focus:after:absolute focus:after:z-[1] focus:after:block focus:after:h-5 focus:after:w-5 focus:after:rounded-full focus:after:content-[''] checked:focus:border-primary checked:focus:bg-primary checked:focus:before:ml-[1.0625rem] checked:focus:before:scale-100 checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca] checked:focus:before:transition-[box-shadow_0.2s,transform_0.2s] dark:bg-neutral-600 dark:after:bg-neutral-400 dark:checked:bg-primary dark:checked:after:bg-primary dark:focus:before:shadow-[3px_-1px_0px_13px_rgba(255,255,255,0.4)] dark:checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca]"
                type="checkbox"
                role="switch"
                id="is_active"
                :disabled="!actionText"
                v-model="values.is_active" />
            </div>
            <div class="flex-auto">
              <i class="text-green-500">Active</i>
            </div>
          </div>
        </div>
        <div class="col-span-8 md:col-span-12">
            <button :disabled="!actionText" @click="addDetail" type="button" class="bg-[#005FBF] hover:bg-[#0055ab] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded">
              <icon fa="plus" /> <span>Add to List</span></button>
        <div class="mt-4">
          <table class="w-full overflow-x-auto table-auto border border-[#CACACA]">
            <thead>
              <tr class="border">
                <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 py-[14.5px] text-center w-[5%] border bg-[#f8f8f8] border-[#CACACA]">No.</td>
                <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[20%] border bg-[#f8f8f8] border-[#CACACA]">Komponen</td>
                <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">Faktor</td>
                <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">Nilai</td>
                <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border w-[20%] bg-[#f8f8f8] border-[#CACACA]">Periode</td>
                <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border w-[5%] bg-[#f8f8f8] border-[#CACACA]">Aksi</td>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, i) in detailArr" :key="item.id" class="border-t" v-if="detailArr.length > 0">
                <td class="p-2 text-center border border-[#CACACA]">
                  {{ i + 1 }}.
                </td>
                <td class="p-2 border border-[#CACACA]">
                  <FieldX :bind="{ readonly: !actionText }" class="!mt-0"
                    :value="item.komponen" @input="v=>item.komponen=v"
                    :errorText="formErrors.komponen?'failed':''" 
                    :hints="formErrors.komponen" label="" placeholder="Tuliskan Komponen" :check="false"
                  />
                </td>
                <td class="p-2 border border-[#CACACA]">
                  <FieldSelect
                    :bind="{ disabled: !actionText, clearable:false }" class="!mt-0 w-full"
                    :value="item.faktor" @input="v=>item.faktor=v"
                    :errorText="formErrors.faktor?'failed':''" 
                    label="" placeholder="Pilih Faktor"
                    :hints="formErrors.faktor"
                    :options="['-','+']"
                    valueField="key" displayField="key" :check="false"
                  />
                </td>
                <td class="p-2 border border-[#CACACA]">
                  <FieldNumber class="!mt-0 w-full"
                    :bind="{ readonly: !actionText }"
                    :value="item.nilai" 
                    @input="(v)=>item.nilai=v"
                    :errorText="formErrors.nilai?'failed':''" 
                    :hints="formErrors.nilai"
                    placeholder="Tuliskan Nilai" label="" :check="false"
                  />
                </td>
                <td class="p-2 border border-[#CACACA]">
                  <FieldSelect
                    :bind="{ disabled: !actionText, clearable:false }" class="!mt-0 w-full"
                    :value="item.periode" @input="v=>item.periode=v"
                    :errorText="formErrors.periode?'failed':''" 
                    label="" placeholder="Pilih Periode"
                    :hints="formErrors.periode"
                    :options="['Bulanan','Harian']"
                    valueField="key" displayField="key" :check="false"
                  />
                </td>
                <td class="p-2 border border-[#CACACA]">
                  <div class="flex justify-center">
                    <button type="button" @click="removeDetail(item)" :disabled="!actionText">
                    <svg width="14" height="18" viewBox="0 0 14 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path id="Vector" d="M14 1H10.5L9.5 0H4.5L3.5 1H0V3H14M1 16C1 16.5304 1.21071 17.0391 1.58579 17.4142C1.96086 17.7893 2.46957 18 3 18H11C11.5304 18 12.0391 17.7893 12.4142 17.4142C12.7893 17.0391 13 16.5304 13 16V4H1V16Z" fill="#F24E1E"/>
                    </svg>
                  </button>
                  </div>

                </td>
              </tr>
              <tr v-else class="text-center">
                <td colspan="7" class="py-[20px]">
                  No data to show
                </td>
              </tr>
            </tbody>
          </table>
      </div>
    </div>
      <!--BUTTON-->
      </div>
      <div class="flex flex-row justify-end space-x-[20px] mt-[5em]">
            <button @click="onBack" class="bg-[#EF4444] hover:bg-[#ed3232] text-white px-[36.5px] py-[12px] rounded-[6px] w-32 ">
            Kembali
          </button>
          <button v-show="actionText" @click="onSave" class="bg-[#10B981] hover:bg-[#0ea774] text-white px-[36.5px] py-[12px] rounded-[6px] w-32 ">
            Simpan
          </button>
      </div>
      <!-- FORM END -->
    </div>
  </div>
</div>
@endverbatim
@endif