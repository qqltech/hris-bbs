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
      <div class="mb-4">
        <h1 class="text-[24px] mb-4 font-bold">
          Form Role
        </h1>
        <hr>
      </div>
      <div class="grid grid-cols-2 gap-3">
         <!-- <div>
          <label for="Direktorat" class="font-semibold select-all">Direktorat <span class="text-red-500 space-x-0 pl-0"></span></label>
          <FieldX :bind="{ readonly: true }" class="w-full py-2 !mt-0"
              :value="values.direktorat" :errorText="formErrors.direktorat?'failed':''"
              @input="v=>values.direktorat=v" :hints="formErrors.direktorat" 
              :check="false"
              label=""
              placeholder=""
          />
        </div> -->
        <!-- START COLUMN -->
        <div>
          <label class="font-semibold">Nama <label class="text-red-500 space-x-0 pl-0"></label></label>
          <FieldX :bind="{ readonly: !actionText }" class=" py-2 !mt-0"
              :value="values.name" :errorname="formErrors.name?'failed':''"
              @input="v=>values.name=v" :hints="formErrors.name" 
              :check="false"
          />
        </div>
        <div class="flex flex-col gap-2 justify-center">
          <label
            class="inline-block pl-[0.15rem] hover:cursor-pointer font-semibold"
            for="is_active"
            >Super Admin :</label
          >
          <div class="flex w-40">
            <div class="flex-auto">
              <i class="text-red-500">No</i>
            </div>
            <div class="flex-auto">
              <input
                class="mr-2 mt-[0.3rem] h-3.5 w-8 appearance-none rounded-[0.4375rem] bg-neutral-300 before:pointer-events-none before:absolute before:h-3.5 before:w-3.5 before:rounded-full before:bg-transparent before:content-[''] after:absolute after:z-[2] after:-mt-[0.1875rem] after:h-5 after:w-5 after:rounded-full after:border-none after:bg-blue-500 after:shadow-[0_0px_3px_0_rgb(0_0_0_/_7%),_0_2px_2px_0_rgb(0_0_0_/_4%)] after:transition-[background-color_0.2s,transform_0.2s] after:content-[''] checked:bg-primary checked:after:absolute checked:after:z-[2] checked:after:-mt-[3px] checked:after:ml-[1.0625rem] checked:after:h-5 checked:after:w-5 checked:after:rounded-full checked:after:border-none checked:after:bg-primary checked:after:shadow-[0_3px_1px_-2px_rgba(0,0,0,0.2),_0_2px_2px_0_rgba(0,0,0,0.14),_0_1px_5px_0_rgba(0,0,0,0.12)] checked:after:transition-[background-color_0.2s,transform_0.2s] checked:after:content-[''] hover:cursor-pointer focus:outline-none focus:ring-0 focus:before:scale-100 focus:before:opacity-[0.12] focus:before:shadow-[3px_-1px_0px_13px_rgba(0,0,0,0.6)] focus:before:transition-[box-shadow_0.2s,transform_0.2s] focus:after:absolute focus:after:z-[1] focus:after:block focus:after:h-5 focus:after:w-5 focus:after:rounded-full focus:after:content-[''] checked:focus:border-primary checked:focus:bg-primary checked:focus:before:ml-[1.0625rem] checked:focus:before:scale-100 checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca] checked:focus:before:transition-[box-shadow_0.2s,transform_0.2s] dark:bg-neutral-600 dark:after:bg-neutral-400 dark:checked:bg-primary dark:checked:after:bg-primary dark:focus:before:shadow-[3px_-1px_0px_13px_rgba(255,255,255,0.4)] dark:checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca]"
                type="checkbox"
                role="switch"
                id="is_active"
                :disabled="!actionText"
                v-model="values.is_superadmin" />
            </div>
            <div class="flex-auto">
              <i class="text-green-500">Yes</i>
            </div>
          </div>
        </div>
        <div class="flex flex-col gap-2 justify-center">
          <label
            class="inline-block pl-[0.15rem] hover:cursor-pointer font-semibold"
            for="is_active"
            >Status :</label
          >
          <div class="flex w-40">
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
        <!-- END COLUMN -->
      </div>

      <!-- table -->

      <div class="p-4 flex items-end" v-if="actionText">
          <ButtonMultiSelect
          title="Tambah Akses"
          @add="onDetailAdd"
          :api="{
            url: `${store.server.url_backend}/operation/m_menu`,
            headers: {'Content-Type': 'Application/json', authorization: `${store.user.token_type} ${store.user.token}`},
            params: { simplest: true,searchfield: 'modul,submodul,menu' },
            onsuccess:(response)=>{
              response.data = [...response.data].map((dt)=>{
                Object.assign(dt,{
                  can_create: true, can_update: true, can_delete: true, can_read: true, role_id: values.role_id, can_verify : false
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
              pinned: true,
              field: 'modul',
              headerName: 'Modul',
              cellClass: ['border-r', '!border-gray-200', 'justify-center'],
              filter:true,
              flex: 1
            },
            {
              pinned: false,
              field: 'submodul',
              headerName: 'Sub Modul',
              cellClass: ['border-r', '!border-gray-200', 'justify-center'],
              filter:true,
              flex: 1
            },
            {
              pinned: false,
              field: 'menu',
              headerName: 'Nama Menu',
              cellClass: ['border-r', '!border-gray-200', 'justify-center'],
              filter:true,
              flex: 1
            },
            ]"
          >
            <div class="flex justify-center w-full h-full items-center px-2 py-1.5 text-xs rounded text-white bg-blue-500 hover:bg-blue-700 hover:bg-blue-600 transition-all duration-200">
              <icon fa="plus" size="sm mr-0.5"/> Tambah Akses
            </div>
          </ButtonMultiSelect>
          <button class="bg-red-500 hover:bg-red-600 text-white font-semibold ml-2 px-2 py-1 rounded-sm flex items-center justify-center mt-2" @click="clearAll">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3 mr-2" viewBox="0 0 16 16">
              <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5ZM11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H2.506a.58.58 0 0 0-.01 0H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1h-.995a.59.59 0 0 0-.01 0H11Zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5h9.916Zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47ZM8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5Z"/>
            </svg>
              Hapus Semua
          </button>
        </div>

      <div>
        <TableStatic
            customClass="h-50vh"
            ref="detail" 
            :value="trx_dtl" 
            @input="onRetotal"
            :columns="[{
                headerName: 'No',
                cellRenderer:'ButtonGrid',
                valueGetter:p=>p.node.rowIndex + 1,
                cellRendererParams:{
                  showValue: true,
                  icon: 'times',
                  class: 'btn-text-danger',
                  click:(app)=>{
                    if (app && app.params) {
                      const row = app.params.node.data
                      swal.fire({
                        icon: 'warning', showDenyButton: true,
                        text: `Hapus Baris ${app.params.node.rowIndex-(-1)}?`,
                      }).then((res) => {
                        if (res.isConfirmed) {
                          app.params.api.applyTransaction({ remove: [app.params.node.data] })
                          trx_dtl.splice(app.params.node.rowIndex, 1)
                        }
                      })
                    }
                  }
                },
                width: 60,
                sortable: false, resizable: true, filter: false,
                cellClass: ['justify-center', 'bg-gray-50']
              },
              {
                flex: 1,
                headerName: 'Nama Menu',
                field: 'menu',
                editable: actionText?true:false,
                sortable: false, resizable: true, filter: false,
                cellClass: ['!border-gray-200'],
                cellEditor: 'FieldNumber',
                cellEditorParams: {
                  input(val, api){
                    api.data['colname']=val
                  }
                }
              },
              {
                headerName: 'Preview',
                field: 'can_read',
                cellClass: ['justify-center', 'border-r','!border-gray-200', '!text-gray-500'],
                width: 100, resizable: false, sortable: false, filter: false,
                cellRenderer: 'ButtonGridCheck',
                cellRendererParams: {
                  readonly: true,
                  change:(app, isChecked)=>{
                    app.params.node.data['can_read'] = isChecked
                    app.params.api.applyTransaction({ update: [app.params.node.data] })
                  }
                }
              },
              {
                headerName: 'Create',
                field: 'can_create',
                cellClass: ['justify-center', 'border-r','!border-gray-200'],
                width: 100, resizable: false, sortable: false, filter: false,
                cellRenderer: 'ButtonGridCheck',
                cellRendererParams: {
                  readonly: !actionText||values.access_id,
                  change:(app, isChecked)=>{
                    app.params.node.data['can_create'] = isChecked
                    app.params.api.applyTransaction({ update: [app.params.node.data] })
                  }
                }
              },
              {
                headerName: 'Update',
                field: 'can_update',
                cellClass: ['justify-center', 'border-r','!border-gray-200'],
                width: 100, resizable: false, sortable: false, filter: false,
                cellRenderer: 'ButtonGridCheck',
                cellRendererParams: {
                  readonly: !actionText||values.access_id,
                  change:(app, isChecked)=>{
                    app.params.node.data['can_update'] = isChecked
                    app.params.api.applyTransaction({ update: [app.params.node.data] })
                  }
                }
              },
              {
                headerName: 'Delete',
                field: 'can_delete',
                cellClass: ['justify-center', 'border-r','!border-gray-200'],
                width: 100, resizable: false, sortable: false, filter: false,
                cellRenderer: 'ButtonGridCheck',
                cellRendererParams: {
                  readonly: !actionText||values.access_id,
                  change:(app, isChecked)=>{
                    app.params.node.data['can_delete'] = isChecked
                    app.params.api.applyTransaction({ update: [app.params.node.data] })
                  }
                }
              },
              {
                headerName: 'Verify',
                field: 'can_verify',
                cellClass: ['justify-center', 'border-r','!border-gray-200'],
                width: 100, resizable: false, sortable: false, filter: false,
                cellRenderer: 'ButtonGridCheck',
                cellRendererParams: {
                  readonly: !actionText||values.access_id,
                  change:(app, isChecked)=>{
                    app.params.node.data['can_verify'] = isChecked
                    app.params.api.applyTransaction({ update: [app.params.node.data] })
                  }
                }
              },
              ]"
            >
            <template #header></template>
          </TableStatic>

      </div>


        <!-- ACTION BUTTON START -->
        <div class="flex flex-row justify-end space-x-[20px] mt-[2em]">
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