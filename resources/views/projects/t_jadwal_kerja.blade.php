@if(!$req->has('id'))
<div class="bg-white p-6 rounded-xl h-[570px]">
  <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions">
    <template #header>
      <RouterLink v-if="currentMenu?.can_create||true||store.user.data.username==='developer'" :to="$route.path+'/create?'+(Date.parse(new Date()))" class="bg-green-500 text-white hover:bg-green-600 rounded-[6px] py-2 px-[12.5px]">
        Tambah
        <icon fa="plus" />
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
         Form Jadwal Kerja
        </h1>
      </div>
      <!-- HEADER END -->
      <!-- FORM START -->
      <div class="grid grid-cols-8 md:grid-cols-12 text-[14px] gap-x-[29px] gap-y-[26px]">
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">Nomor<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: true }" 
              type="text" 
              :value="values.nomor"
              class="col-span-12 !mt-0 w-full"
              @input="v=>values.nomor=v" 
              :check="false"
              /> 
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">Direktorat<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: true }" 
              :value="values.m_dir" :errorText="formErrors.m_dir?'failed':''"
              @input="v=>values.m_dir=v" :hints="formErrors.m_dir" 
               :check="false"
               class="col-span-12 !mt-0 w-full"
            />   
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">Divisi<label class="text-red-500 space-x-0 pl-0">*</label></label>
       
            <FieldX :bind="{ readonly: true }" 
              :value="values.m_divisi" :errorText="formErrors.m_divisi?'failed':''"
              @input="v=>values.m_divisi=v" :hints="formErrors.m_divisi" 
               :check="false"
               class="col-span-12 !mt-0 w-full"
            />
            
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">Departemen<label class="text-red-500 space-x-0 pl-0">*</label></label>
           <FieldX :bind="{ readonly: true }" 
              :value="values.m_dept" :errorText="formErrors.m_dept?'failed':''"
              @input="v=>values.m_dept=v" :hints="formErrors.m_dept" 
               :check="false"
               class="col-span-12 !mt-0 w-full"
            />
          </div>
        </div>

         <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2 relative">
            <label class="col-span-12">Group Kerja<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: true }" 
              :value="values.group_kerja" :errorText="formErrors.group_kerja?'failed':''"
              @input="v=>values.group_kerja=v" :hints="formErrors.group_kerja" 
              :check="false"
               class="col-span-12 !mt-0 w-full"
            />
            
            <button class="absolute top-0 bottom-0 right-0 left-0 " @click="onOpenGK" ></button>

          </div>
        </div>
        
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">Keterangan<label class="text-red-500 space-x-0 pl-0">*</label></label>
           <FieldX :bind="{ readonly: false }" 
              type="textarea" 
              :value="values.keterangan"
              class="col-span-12 !mt-0 w-full"
              @input="v=>values.keterangan=v" 
              :check="false"
              />        
          </div>
        </div>


       <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center gap-y-2">
            <label class="col-span-12">Status<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: true }" 
              type="text" 
              :value="values.status"
              class="col-span-12 !mt-0 w-full"
              @input="v=>values.status=v" 
              :check="false"
              /> 
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
              <i class="text-red-500">InActive</i>
            </div>
            <div class="flex-auto">
              <input
                class="mr-2 mt-[0.3rem] h-3.5 w-8 appearance-none rounded-[0.4375rem] bg-neutral-300 before:pointer-events-none before:absolute before:h-3.5 before:w-3.5 before:rounded-full before:bg-transparent before:content-[''] after:absolute after:z-[2] after:-mt-[0.1875rem] after:h-5 after:w-5 after:rounded-full after:border-none after:bg-blue-500 after:shadow-[0_0px_3px_0_rgb(0_0_0_/_7%),_0_2px_2px_0_rgb(0_0_0_/_4%)] after:transition-[background-color_0.2s,transform_0.2s] after:content-[''] checked:bg-primary checked:after:absolute checked:after:z-[2] checked:after:-mt-[3px] checked:after:ml-[1.0625rem] checked:after:h-5 checked:after:w-5 checked:after:rounded-full checked:after:border-none checked:after:bg-primary checked:after:shadow-[0_3px_1px_-2px_rgba(0,0,0,0.2),_0_2px_2px_0_rgba(0,0,0,0.14),_0_1px_5px_0_rgba(0,0,0,0.12)] checked:after:transition-[background-color_0.2s,transform_0.2s] checked:after:content-[''] hover:cursor-pointer focus:outline-none focus:ring-0 focus:before:scale-100 focus:before:opacity-[0.12] focus:before:shadow-[3px_-1px_0px_13px_rgba(0,0,0,0.6)] focus:before:transition-[box-shadow_0.2s,transform_0.2s] focus:after:absolute focus:after:z-[1] focus:after:block focus:after:h-5 focus:after:w-5 focus:after:rounded-full focus:after:content-[''] checked:focus:border-primary checked:focus:bg-primary checked:focus:before:ml-[1.0625rem] checked:focus:before:scale-100 checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca] checked:focus:before:transition-[box-shadow_0.2s,transform_0.2s] dark:bg-neutral-600 dark:after:bg-neutral-400 dark:checked:bg-primary dark:checked:after:bg-primary dark:focus:before:shadow-[3px_-1px_0px_13px_rgba(255,255,255,0.4)] dark:checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca]"
                type="checkbox"
                role="switch"
                id="is_active"
                :disabled="!actionText"
                v-model="values.status" />
            </div>
            <div class="flex-auto">
              <i class="text-green-500">Active</i>
            </div>
          </div>
        </div> -->

      <!--BUTTON-->
      </div>

       <button @click="onGenerate" class="bg-[#005FBF] hover:bg-[#007FBF] mt-8 text-white px-[36.5px] py-[12px] rounded-[6px] w-32 ">
            Generate
      </button>

      <!-- table karyawan -->

      <div class="w-full mt-8 max-h-96 overflow-y-scroll">
        <table class="w-full ">
          <thead>
            <tr>
              <th class="border border-gray-400 bg-gray-200 p-3 text-center text-gray-400 rounded-tl-md">
                No
              </th>
               <th class="border border-gray-400 bg-gray-200 p-3 text-center text-gray-400">
                NIK
              </th>
               <th class="border border-gray-400 bg-gray-200 p-3 text-center text-gray-400">
                karyawan
              </th>
               <th class="border border-gray-400 bg-gray-200 p-3 text-center text-gray-400">
                Posisi
              </th>
               <th class="border border-gray-400 bg-gray-200 p-3 text-center text-gray-400">
                Jam Kerja
              </th>
               <th class="border border-gray-400 bg-gray-200 p-3 text-center text-gray-400">
                Aksi
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item, i in values.t_jadwal_kerja_det">
              <td class="border border-gray-400 p-3 text-center rounded-tl-md">
                {{i + 1}}
              </td>
               <td class="border border-gray-400 p-3 text-center">
                {{item.nik}}
              </td>
               <td class="border border-gray-400 p-3 text-center">
                {{item.nama_lengkap}}
              </td>
               <td class="border border-gray-400 p-3 text-center">
                {{item["posisi.nama"]}}
              </td>
               <td class="border border-gray-400 p-3 text-center">
                {{item.m_jam_kerja_id}}
              </td>
               <td class="border border-gray-400 p-3 text-center">
                <button @click="deleteJKD(i)" class="w-fit">
                   <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-red-500 ">
                       <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- modal -->

      <div v-if="modal" class="absolute w-screen h-screen overflow-hidden flex items-center justify-center  top-0 bottom-0 right-0 left-0">

          <div class="w-[80%] bg-white border border-gray-200 rounded-lg p-6 shadow-lg">
            <div class="flex justify-between items-center w-full mb-4">
              <h1 class="font-bold w-fit text-xl">List Group Kerja</h1>
              <button class="w-fit font-bold" @click="onOpenGK">X</button>
            </div>

            <div class="w-full border border-gray-200 select-none h-52 overflow-y-scroll">
              <table class="w-full">
                <thead>
                  <tr>
                  <th class="border border-gray-200 py-2 px-3">select</th>
                  <th class="border border-gray-200 py-2 px-3">Direktorat</th>
                  <th class="border border-gray-200 py-2 px-3">Divsi</th>
                  <th class="border border-gray-200 py-2 px-3">Departemen</th>
                  <th class="border border-gray-200 py-2 px-3">Tanggal Mulai</th>
                  <th class="border border-gray-200 py-2 px-3">Tanggal Berakhir</th>
                  <th class="border border-gray-200 py-2 px-3">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in groupKerja" :key="item.id">
                  <td class="border border-gray-200 py-2 px-3 text-center">
                    
                    <input type="button" @click="selectGK(item)" class="p-3 rounded-md bg-green-300 cursor-pointer" value="Select" />
                  </td>
                  <td class="border border-gray-200 py-2 px-3 text-center">{{item["m_dept.nama"]}}</td>
                  <td class="border border-gray-200 py-2 px-3 text-center">{{item['m_divisi.nama']}}</td>
                  <td class="border border-gray-200 py-2 px-3 text-center">{{item["m_dept.nama"]}}</td>
                  <td class="border border-gray-200 py-2 px-3 text-center">{{item?.date_from}}</td>
                  <td class="border border-gray-200 py-2 px-3 text-center">{{item?.date_to}}</td>
                  <td class="border border-gray-200 py-2 px-3 text-center">{{item?.status}}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

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