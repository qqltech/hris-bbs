@verbatim
  <div class="container mx-auto p-4 ">
        <div class="bg-gray-800 text-white p-4 mb-4 text-center">
            <h1 class="text-3xl font-bold">Jadwal Pengambilan Makan Siang</h1>
        </div>
        
        <div class="w-1/2 float-left p-4 bg-gray-200">
            <div class="p-4 flex items-end">
              <ButtonMultiSelect
                title="Pilih Karyawan"
                @add="onDetailAdd"
                :api="{
                  url: `${store.server.url_backend}/operation/m_kary`,
                  headers: {'Content-Type': 'Application/json', authorization: `${store.user.token_type} ${store.user.token}`},
                  params: { 
                    simplest: true,
                    selectfield: 'id,nik,nama_lengkap,m_dept.nama',
                    searchfield: 'nik,nama_lengkap,m_dept.nama'
                  },
                  onsuccess:(response)=>{
                    response.data = [...response.data].map((dt)=>{
                      Object.assign(dt,{
                        can_create: true, can_update: true, can_delete: true, can_read: true, role_id: values.role_id
                      })
                      return dt
                    })
                    response.page = 1
                    response.hasNext = false
                    return response
                  }
                }"
                  :columns="[{
                    checkboxSelection: true,
                    headerCheckboxSelection: true,
                    headerName: 'No',
                    valueGetter:(params)=>{
                      return ''
                    },
                    width: 60,
                    sortable: false, resizable: false, filter: false,
                    cellClass: ['justify-center', 'bg-gray-50']
                  },
                  {
                    headerName: 'No',
                    valueGetter:(p)=>p.node.rowIndex + 1,
                    width: 60,
                    sortable: false, resizable: false, filter: false,
                    cellClass: ['border-r', '!border-gray-200', 'justify-left'],
                  },
                  {
                    pinned: false,
                    field: 'nik',
                    headerName: 'NIK',
                    cellClass: ['border-r', '!border-gray-200', 'justify-center'],
                    filter:false,
                    flex: 1
                  },
                  {
                    flex: 1,
                    field: 'nama_lengkap',
                    headerName:  'Nama Karyawan',
                    sortable: false, resizable: true, filter: false,
                    cellClass: ['border-r', '!border-gray-200', 'justify-center']
                  },]"
                >
                  <div class="flex justify-center w-full h-full items-center px-2 py-1.5 text-xs rounded text-white bg-blue-500 hover:bg-blue-700 hover:bg-blue-600 transition-all duration-200">
                    <icon fa="plus" size="sm mr-0.5"/> Pilih Karyawan
                  </div>
                </ButtonMultiSelect>
                
            </div>
            <table class="w-1/2 overflow-x-auto table-auto border border-[#CACACA]">
            <thead>
              <tr class="border">
                <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 py-[14.5px] text-center w-[5%] border bg-[#f8f8f8] border-[#CACACA]">No.</td>
                <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] w-[40%] border-[#CACACA]">NIK</td>
                <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">Nama</td>
                <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">Dept.</td>
                <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border w-[5%] bg-[#f8f8f8] border-[#CACACA]">Aksi</td>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, i) in detailArrKaryawan" :key="item.__id" class="border-t" v-if="detailArrKaryawan > 0">
                <td class="p-2 text-center border border-[#CACACA]">
                  {{ i + 1 }}.
                </td>
                <td class="text-left border border-[#CACACA] px-2">
                  {{ item.nik }}
                </td>
                <td class="text-left border border-[#CACACA] px-2">
                  {{ item.nama }}
                </td>
                <td class="text-left border border-[#CACACA] px-2">
                  {{ item['m_dept_nama'] }}
                </td>
                <td class="p-2 border border-[#CACACA]">
                  <div class="flex justify-center">
                    <button type="button" @click="removeDetail(item)" :disabled="!actionText">
                    <svg width="10" height="15" viewBox="0 0 14 18" fill="none" xmlns="http://www.w3.org/2000/svg">
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

        <div class="w-1/2 float-left p-4 bg-gray-300">
            <h2 class="text-2xl font-bold">Kolom 2</h2>
            <p>Isi kolom 2 disini...</p>
        </div>
    </div>
@endverbatim