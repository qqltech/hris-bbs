@if(!$req->has('id'))
<div class="bg-white p-6 rounded-xl h-[570px]">
  <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions">
    <template #header >
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
          Form Realisasi Perjalanan Dinas
        </h1>
      </div>
      <!-- HEADER END -->

      <!-- FORM START -->
      <div class="grid grid-cols-8 md:grid-cols-12 text-[14px] gap-x-[29px] gap-y-[26px]">
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">Nomor<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: true }"  class="col-span-12 !mt-0 w-full"
              :value="values.nomor" :errorText="formErrors.nomor?'failed':''"
              @input="v=>values.nomor=v" :hints="formErrors.nomor" :check="false"
              label="" placeholder=""
            />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">Nomor SPD<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldPopup
              :bind="{ readonly: !actionText }" class="col-span-12 !mt-0 w-full"
              :value="values.t_spd_id" @input="(v)=>values.t_spd_id=v"
              :errorText="formErrors.t_spd_id?'failed':''" 
              :hints="formErrors.t_spd_id" 
              @update:valueFull="(objVal)=>{
                  if(objVal){
                    values.direktorat = objVal['m_dir.nama']
                    values.divisi = objVal['m_divisi.nama']
                    values.departemen = objVal['m_dept.nama']
                    values.posisi = objVal['m_posisi.desc_kerja']
                    values.tanggal = objVal.tanggal
                    values.tgl_awal = objVal.tgl_acara_awal
                    values.tgl_akhir = objVal.tgl_acara_akhir
                    values.jml_hari = objVal.tgl_acara_akhir.split('/')[0] - objVal.tgl_acara_awal.split('/')[0]
                    values.zona_asal = objVal['m_zona_asal.nama']
                    values.zona_tujuan = objVal['m_zona_tujuan.nama']
                    values.lokasi_tujuan = objVal['m_lokasi_tujuan.nama']
                    values.nik = objVal['m_kary.nik']
                    values.pic = objVal['pic.nama_lengkap']
                    values.total_biaya_spd = objVal.total_biaya
                    values.is_kend_dinas = objVal.is_kend_dinas
                    getDetailSPD()
                  }else if(objVal === null){
                    values.direktorat = null 
                    values.divisi = null 
                    values.departemen = null 
                    values.posisi = null 
                    values.tgl_awal = null 
                    values.tgl_akhir = null 
                    values.jml_hari = null 
                    values.zona_asal = null 
                    values.zona_tujuan = null 
                    values.lokasi_tujuan = null 
                    values.nik = null 
                    values.pic = null 
                    values.total_biaya_spd = null 
                    values.is_kend_dinas = null
                    detailArr = []
                  }
                  

                }"
              valueField="id" displayField="nomor"
              :api="{
                url: `${store.server.url_backend}/operation/t_spd`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  simplest:true,
                  where: `this.status = 'APPROVED'`,
                  searchfield: 'this.nomor, this.id, m_kary.nama_lengkap, pic.nama_lengkap, m_zona_tujuan.nama, m_lokasi_tujuan.nama'
                }
              }"
              placeholder="Pilih Nomor SPD" label="" :check="false" 
              :columns="[{
                headerName: 'No',
                valueGetter:(p)=>p.node.rowIndex + 1,
                width: 60,
                sortable: false, resizable: false, filter: false,
                cellClass: ['justify-center', 'bg-gray-50']
              },
              {
                flex: 1,
                field: 'nomor',
                sortable: false, resizable: true, filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },
              {
                flex: 1,
                field: 'm_kary.nama_lengkap',
                headerName: 'Karyawan',
                sortable: false, resizable: true, filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },
              {
                flex: 1,
                field: 'pic.nama_lengkap',
                headerName: 'PIC',
                sortable: false, resizable: true, filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },
              {
                flex: 1,
                field: 'm_zona_tujuan.nama',
                headerName: 'Zona',
                sortable: false, resizable: true, filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },
              {
                flex: 1,
                field: 'm_lokasi_tujuan.nama',
                headerName: 'Lokasi',
                sortable: false, resizable: true, filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },
              ]"
            />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">Direktorat<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: true }"  class="col-span-12 !mt-0 w-full"
              :value="values.direktorat" :errorText="formErrors.direktorat?'failed':''"
              @input="v=>values.direktorat=v" :hints="formErrors.direktorat" :check="false"
              label="" placeholder=""
            />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">Divisi<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: true }"  class="col-span-12 !mt-0 w-full"
              :value="values.divisi" :errorText="formErrors.divisi?'failed':''"
              @input="v=>values.divisi=v" :hints="formErrors.divisi" :check="false"
              label="" placeholder=""
            />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">Departemen<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: true }"  class="col-span-12 !mt-0 w-full"
              :value="values.departemen" :errorText="formErrors.departemen?'failed':''"
              @input="v=>values.departemen=v" :hints="formErrors.departemen" :check="false"
              label="" placeholder=""
            />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">Posisi<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: true }"  class="col-span-12 !mt-0 w-full"
              :value="values.posisi" :errorText="formErrors.posisi?'failed':''"
              @input="v=>values.posisi=v" :hints="formErrors.posisi" :check="false"
              label="" placeholder=""
            />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">Tanggal<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: true, disabled: true }" type="date"  class="col-span-12 !mt-0 w-full"
              :value="values.tanggal" :errorText="formErrors.tanggal?'failed':''"
              @input="v=>values.tanggal=v" :hints="formErrors.tanggal" :check="false"
              label="" placeholder=""
            />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">Tanggal Acara Awal<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: true, disabled: true }" type="date" class="col-span-12 !mt-0 w-full"
              :value="values.tgl_awal" :errorText="formErrors.tgl_awal?'failed':''"
              @input="v=>values.tgl_awal=v" :hints="formErrors.tgl_awal" :check="false"
              label="" placeholder=""
            />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">Tanggal Acara Akhir<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: true, disabled: true }" type="date" class="col-span-12 !mt-0 w-full"
              :value="values.tgl_akhir" :errorText="formErrors.tgl_akhir?'failed':''"
              @input="v=>values.tgl_akhir=v" :hints="formErrors.tgl_akhir" :check="false"
              label="" placeholder=""
            />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">Jumlah Hari<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: true }" class="col-span-12 !mt-0 w-full"
              :value="values.jml_hari" :errorText="formErrors.jml_hari?'failed':''"
              @input="v=>values.jml_hari=v" :hints="formErrors.jml_hari" :check="false"
              label="" placeholder=""
            />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">Zona Asal<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: true }" class="col-span-12 !mt-0 w-full"
              :value="values.zona_asal" :errorText="formErrors.zona_asal?'failed':''"
              @input="v=>values.zona_asal=v" :hints="formErrors.zona_asal" :check="false"
              label="" placeholder=""
            />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">Zona Tujuan<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: true }" class="col-span-12 !mt-0 w-full"
              :value="values.zona_tujuan" :errorText="formErrors.zona_tujuan?'failed':''"
              @input="v=>values.zona_tujuan=v" :hints="formErrors.zona_tujuan" :check="false"
              label="" placeholder=""
            />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">Lokasi Tujuan<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: true }" class="col-span-12 !mt-0 w-full"
              :value="values.lokasi_tujuan" :errorText="formErrors.lokasi_tujuan?'failed':''"
              @input="v=>values.lokasi_tujuan=v" :hints="formErrors.lokasi_tujuan" :check="false"
              label="" placeholder=""
            />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">NIK<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: true }" class="col-span-12 !mt-0 w-full"
              :value="values.nik" :errorText="formErrors.nik?'failed':''"
              @input="v=>values.nik=v" :hints="formErrors.nik" :check="false"
              label="" placeholder=""
            />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">PIC<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: true }" class="col-span-12 !mt-0 w-full"
              :value="values.pic" :errorText="formErrors.pic?'failed':''"
              @input="v=>values.pic=v" :hints="formErrors.pic" :check="false"
              label="" placeholder=""
            />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">Total Biaya<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldNumber class="col-span-12 !mt-0 w-full"
              :bind="{ readonly: true }"
              :value="values.total_biaya_spd" @input="(v)=>values.total_biaya_spd=v"
              :errorText="formErrors.total_biaya_spd?'failed':''" 
              :hints="formErrors.total_biaya_spd"
              placeholder="" label="" :check="false"
            />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">Total Biaya Rencana Selisih<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldNumber class="col-span-12 !mt-0 w-full"
              :bind="{ readonly: false }"
              :value="values.total_biaya_selisih" @input="(v)=>values.total_biaya_selisih=v"
              :errorText="formErrors.total_biaya_selisih?'failed':''" 
              :hints="formErrors.total_biaya_selisih"
              placeholder="" label="" :check="false"
            />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">Keterangan<label class="text-red-500 space-x-0 pl-0"></label></label>
            <FieldX :bind="{ readonly: !actionText }" type="textarea" class="col-span-12 !mt-0 w-full"
              :value="values.keterangan" :errorText="formErrors.keterangan?'failed':''"
              @input="v=>values.keterangan=v" :hints="formErrors.keterangan" :check="false"
              label="" placeholder="Tuliskan Keterangan"
            />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">Status<label class="text-red-500 space-x-0 pl-0"></label></label>
            <FieldX :bind="{ readonly: true }" class="col-span-12 !mt-0 w-full"
              :value="values.status" :errorText="formErrors.status?'failed':''"
              @input="v=>values.status=v" :hints="formErrors.status" :check="false"
              label="" placeholder=""
            />
          </div>
        </div>
        <div class="flex flex-col gap-4 col-span-6">
          <label
            class="inline-block pl-[0.15rem] hover:cursor-pointer font-semibold"
            for="is_kend"
            >Kendaraan Dinas :</label
          >
          <div class="flex w-40">
            <div class="flex-auto">
              <i class="text-red-500">Tidak</i>
            </div>
            <div class="flex-auto">
              <input
                class="mr-2 mt-[0.3rem] h-3.5 w-8 appearance-none rounded-[0.4375rem] bg-neutral-300 before:pointer-events-none before:absolute before:h-3.5 before:w-3.5 before:rounded-full before:bg-transparent before:content-[''] after:absolute after:z-[2] after:-mt-[0.1875rem] after:h-5 after:w-5 after:rounded-full after:border-none after:bg-gray-500 after:shadow-[0_0px_3px_0_rgb(0_0_0_/_7%),_0_2px_2px_0_rgb(0_0_0_/_4%)] after:transition-[background-color_0.2s,transform_0.2s] after:content-[''] checked:bg-primary checked:after:absolute checked:after:z-[2] checked:after:-mt-[3px] checked:after:ml-[1.0625rem] checked:after:h-5 checked:after:w-5 checked:after:rounded-full checked:after:border-none checked:after:bg-primary checked:after:shadow-[0_3px_1px_-2px_rgba(0,0,0,0.2),_0_2px_2px_0_rgba(0,0,0,0.14),_0_1px_5px_0_rgba(0,0,0,0.12)] checked:after:transition-[background-color_0.2s,transform_0.2s] checked:after:content-[''] hover:cursor-pointer focus:outline-none focus:ring-0 focus:before:scale-100 focus:before:opacity-[0.12] focus:before:shadow-[3px_-1px_0px_13px_rgba(0,0,0,0.6)] focus:before:transition-[box-shadow_0.2s,transform_0.2s] focus:after:absolute focus:after:z-[1] focus:after:block focus:after:h-5 focus:after:w-5 focus:after:rounded-full focus:after:content-[''] checked:focus:border-primary checked:focus:bg-primary checked:focus:before:ml-[1.0625rem] checked:focus:before:scale-100 checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca] checked:focus:before:transition-[box-shadow_0.2s,transform_0.2s] dark:bg-neutral-600 dark:after:bg-neutral-400 dark:checked:bg-primary dark:checked:after:bg-primary dark:focus:before:shadow-[3px_-1px_0px_13px_rgba(255,255,255,0.4)] dark:checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca]"
                :class="{'after:bg-blue-500': values.is_kend_dinas === true}"
                type="checkbox"
                role="switch"
                id="is_kend"
                :disabled="true"
                v-model="values.is_kend_dinas"
                />
            </div>
            <div class="flex-auto">
              <i class="text-green-500">Iya</i>
            </div>
          </div>
        </div>
        <!-- <div class="flex flex-col gap-2">
          <label
            class="inline-block pl-[0.15rem] hover:cursor-pointer"
            for="is_active"
            >Status</label
          >
          <div class="flex w-40 items-center">
            <div class="flex-auto">
              <i class="text-red-500">Draft</i>
            </div>
            <div class="flex-auto">
              <input
                class="mr-2 mt-[0.3rem] h-3.5 w-8 appearance-none rounded-[0.4375rem] bg-neutral-300 before:pointer-events-none before:absolute before:h-3.5 before:w-3.5 before:rounded-full before:bg-transparent before:content-[''] after:absolute after:z-[2] after:-mt-[0.1875rem] after:h-5 after:w-5 after:rounded-full after:border-none after:bg-blue-500 after:shadow-[0_0px_3px_0_rgb(0_0_0_/_7%),_0_2px_2px_0_rgb(0_0_0_/_4%)] after:transition-[background-color_0.2s,transform_0.2s] after:content-[''] checked:bg-primary checked:after:absolute checked:after:z-[2] checked:after:-mt-[3px] checked:after:ml-[1.0625rem] checked:after:h-5 checked:after:w-5 checked:after:rounded-full checked:after:border-none checked:after:bg-primary checked:after:shadow-[0_3px_1px_-2px_rgba(0,0,0,0.2),_0_2px_2px_0_rgba(0,0,0,0.14),_0_1px_5px_0_rgba(0,0,0,0.12)] checked:after:transition-[background-color_0.2s,transform_0.2s] checked:after:content-[''] hover:cursor-pointer focus:outline-none focus:ring-0 focus:before:scale-100 focus:before:opacity-[0.12] focus:before:shadow-[3px_-1px_0px_13px_rgba(0,0,0,0.6)] focus:before:transition-[box-shadow_0.2s,transform_0.2s] focus:after:absolute focus:after:z-[1] focus:after:block focus:after:h-5 focus:after:w-5 focus:after:rounded-full focus:after:content-[''] checked:focus:border-primary checked:focus:bg-primary checked:focus:before:ml-[1.0625rem] checked:focus:before:scale-100 checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca] checked:focus:before:transition-[box-shadow_0.2s,transform_0.2s] dark:bg-neutral-600 dark:after:bg-neutral-400 dark:checked:bg-primary dark:checked:after:bg-primary dark:focus:before:shadow-[3px_-1px_0px_13px_rgba(255,255,255,0.4)] dark:checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca]"
                :class="{'after:bg-blue-500': values.status === true}"
                type="checkbox"
                role="switch"
                id="is_active"
                :disabled="!actionText"
                v-model="values.status" />
            </div>
            <div class="flex-auto">
              <i class="text-green-500">Posted</i>
            </div>
          </div>
        </div> -->
        <div class="col-span-8 md:col-span-12">
            <button :disabled="!actionText" @click="addDetail" type="button" class="bg-[#005FBF] hover:bg-[#0055ab] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded">
              <icon fa="plus" /> <span>Add to List</span></button>
        <div class="mt-4">
          <table class="w-full overflow-x-auto table-auto border border-[#CACACA]">
            <thead>
              <tr class="border">
                <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 py-[14.5px] text-center w-[5%] border bg-[#f8f8f8] border-[#CACACA]">No.</td>
                <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border w-[15%] bg-[#f8f8f8] border-[#CACACA]">Tipe</td>
                <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">Biaya</td>
                <!-- <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border w-[20%] bg-[#f8f8f8] border-[#CACACA]">Kendaraan Dinas</td> -->
                <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">Keterangan</td>
                <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">Biaya Realisasi</td>
                <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">Catatan Realisasi</td>
                <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border w-[5%] bg-[#f8f8f8] border-[#CACACA]">Aksi</td>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, i) in detailArr" :key="item.id" class="border-t" v-if="detailArr.length > 0">
                <td class="p-2 text-center border border-[#CACACA]">
                  {{ i + 1 }}.
                </td>
                <td class="p-2 border border-[#CACACA]">
                  <FieldSelect
                    :bind="{ disabled: item.isDisabled, clearable:false }" class="!mt-0 w-full"
                    :value="item.tipe_spd_id" @input="v=>item.tipe_spd_id=v"
                    :errorText="formErrors.tipe_spd_id?'failed':''" 
                    label="" placeholder="Pilih Tipe SPD"
                    :hints="formErrors.tipe_spd_id"
                    :api="{
                        url: `${store.server.url_backend}/operation/m_general`,
                        headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                        params: {
                          where: `this.group='TIPE SPD' AND this.is_active='true'`
                        }
                    }"
                    valueField="id" displayField="value" :check="false"
                  />
                </td>
                <td class="p-2 border border-[#CACACA]">
                  <FieldNumber class="!mt-0 w-full"
                    :bind="{ readonly: item.isDisabled }"
                    :value="item.biaya" 
                    @input="(v)=>item.biaya=v"
                    :errorText="formErrors.biaya?'failed':''" 
                    :hints="formErrors.biaya"
                    placeholder="Tuliskan Biaya" label="" :check="false"
                  />
                </td>
                <!-- <td class="p-2 border border-[#CACACA]">
                  <div class="flex justify-center items-center" :class="{'space-x-4':item.is_kendaraan_dinas}">
                    <input
                      class="mr-2 mt-[0.3rem] h-3.5 w-8 appearance-none rounded-[0.4375rem] bg-neutral-300 before:pointer-events-none before:absolute before:h-3.5 before:w-3.5 before:rounded-full before:bg-transparent before:content-[''] after:absolute after:z-[2] after:-mt-[0.1875rem] after:h-5 after:w-5 after:rounded-full after:border-none after:bg-gray-500 after:shadow-[0_0px_3px_0_rgb(0_0_0_/_7%),_0_2px_2px_0_rgb(0_0_0_/_4%)] after:transition-[background-color_0.2s,transform_0.2s] after:content-[''] checked:bg-primary checked:after:absolute checked:after:z-[2] checked:after:-mt-[3px] checked:after:ml-[1.0625rem] checked:after:h-5 checked:after:w-5 checked:after:rounded-full checked:after:border-none checked:after:bg-primary checked:after:shadow-[0_3px_1px_-2px_rgba(0,0,0,0.2),_0_2px_2px_0_rgba(0,0,0,0.14),_0_1px_5px_0_rgba(0,0,0,0.12)] checked:after:transition-[background-color_0.2s,transform_0.2s] checked:after:content-[''] hover:cursor-pointer focus:outline-none focus:ring-0 focus:before:scale-100 focus:before:opacity-[0.12] focus:before:shadow-[3px_-1px_0px_13px_rgba(0,0,0,0.6)] focus:before:transition-[box-shadow_0.2s,transform_0.2s] focus:after:absolute focus:after:z-[1] focus:after:block focus:after:h-5 focus:after:w-5 focus:after:rounded-full focus:after:content-[''] checked:focus:border-primary checked:focus:bg-primary checked:focus:before:ml-[1.0625rem] checked:focus:before:scale-100 checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca] checked:focus:before:transition-[box-shadow_0.2s,transform_0.2s] dark:bg-neutral-600 dark:after:bg-neutral-400 dark:checked:bg-primary dark:checked:after:bg-primary dark:focus:before:shadow-[3px_-1px_0px_13px_rgba(255,255,255,0.4)] dark:checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca]"
                      :class="{'!after:bg-blue-500': item.is_kendaraan_dinas === true}"
                      type="checkbox"
                      role="switch"
                      id="is_active"
                      :disabled="item.is_knd_dinas"
                      v-model="item.is_kendaraan_dinas" />
                      <FieldPopup class="!mt-0 w-full" v-if="item.is_kendaraan_dinas"
                        :bind="{ readonly: item.is_knd_dinas }"
                        :value="item.m_knd_dinas_id" @input="(v)=>item.m_knd_dinas_id=v"
                        :errorText="formErrors.m_knd_dinas_id?'failed':''" 
                        :hints="formErrors.m_knd_dinas_id" 
                        valueField="id" displayField="nama"
                        :api="{
                          url:  `${store.server.url_backend}/operation/m_knd_dinas`,
                          headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                          params: {
                              where: `this.is_active='true'`
                            }
                        }"
                        placeholder="Pilih Kendaraan Dinas" label="" :check="false" 
                        :columns="[{
                          headerName: 'No',
                          valueGetter:(p)=>p.node.rowIndex + 1,
                          width: 60,
                          sortable: false, resizable: false, filter: false,
                          cellClass: ['justify-center', 'bg-gray-50']
                        },
                        {
                          flex: 1,
                          field: 'nama',
                          headerName:  'Kendaraan',
                          sortable: false, resizable: true, filter: 'ColFilter',
                          cellClass: ['border-r', '!border-gray-200', 'justify-center']
                        },
                        {
                          flex: 1,
                          field: 'nopol',
                          headerName:  'Plat Nomer',
                          sortable: false, resizable: true, filter: 'ColFilter',
                          cellClass: ['border-r', '!border-gray-200', 'justify-center']
                        }
                        ]"
                      />
                  </div>
                </td> -->
                <td class="p-2 border border-[#CACACA]">
                  <FieldX :bind="{ readonly: item.isDisabled }" class="!mt-0"
                    :value="item.keterangan" @input="v=>item.keterangan=v" label="" placeholder="Tuliskan Keterangan" :check="false"
                  />
                </td>
                <td class="p-2 border border-[#CACACA]">
                  <FieldNumber
                    :bind="{ readonly: !actionText }" class="!mt-0"
                    :value="item.biaya_realisasi" 
                    @input="(v)=>{
                      item.biaya_realisasi=v
                      countBiayaSelisih()
                      }"
                    placeholder="Tuliskan Biaya" label="" :check="false"
                  />
                </td>
                <td class="p-2 border border-[#CACACA]">
                  <FieldX :bind="{ readonly: !actionText }" class="!mt-0"
                    :value="item.catatan_realisasi" @input="v=>item.catatan_realisasi=v" placeholder="Tuliskan Catatan Realisasi" label="" :check="false"
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
      
      <div class="flex flex-row justify-end space-x-[20px] mt-[5em]">
            <button @click="onBack" class="bg-[#EF4444] hover:bg-[#ed3232] text-white px-[36.5px] py-[12px] rounded-[6px] ">
            Kembali
          </button>
          <button v-show="route.query.action?.toLowerCase() === 'verifikasi'" @click="posted" class="bg-orange-500 hover:bg-orange-600 text-white px-[36.5px] py-[12px] rounded-[6px] ">
            Posted
          </button>
          <button v-show="actionText" @click="onSave" class="bg-[#10B981] hover:bg-[#0ea774] text-white px-[36.5px] py-[12px] rounded-[6px] ">
            Simpan
          </button>
      </div>
      <!-- FORM END -->
    </div>
  </div>
</div>
@endverbatim
@endif