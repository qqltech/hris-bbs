@verbatim
<div class="flex flex-col border shadow-sm px-6 py-6 <md:w-full w-full bg-white">
   <div class="flex items-center justify-between mb-2 pb-4">
    <div class="w-[100%]">
      <div class="flex items-center justify-between">
      <FieldX  
        class="w-70 mb-2 mt-2"
        :bind="{ readonly: false }"
        :value="values.search" 
        @input="v=>values.search=v"
        @keyup.enter="search()"
        label=""
        placeholder="Cari"
        :check="false" />
      <div class="flex justify-end">
        <button
          class="py-1.5 px-2 border-gray-300 border-2 rounded-[5px] text-gray-400"
          title="Segarkan"
          @click="loadTable()"
          >
          <Icon fa="arrow-rotate-right">
        </button>
        <FieldSelect
          class="w-20 ml-2 !mt-0"
          :bind="{ disabled: false, clearable: false }"
          :value="100" 
          :check="false"
          @input="(v)=>{
            values.paginate=v
            paginate()
          }"
          :options="[50,100,200,500]"
          fa-icon="table" :check="true" />
        </div>
      </div>
      <div class="flex items-center justify-between">
        <div class="w-[100%] h-[440px] overflow-y-scroll">
          <div >
            <tbody v-if="!dataLanding.items.length">
              <tr 
                class="w-[100%] bg-white border-b dark:bg-gray-800 dark:border-gray-700" 
              >
                <th scope="row" class="w-[100%] px-6 py-2 font-medium text-blue-700 whitespace-nowrap dark:text-white">
                    Data approval tidak ditemukan
                </th>
              </tr>
            <tbody>
          </div>
          <div
            v-else
            v-for="(item,i) in dataLanding.items" :key="i"
            class="rounded-none border border-l-0 border-r-0 border-t-0 border-neutral-200 !bg-yellow-300 dark:border-neutral-600 dark:bg-neutral-800">
            <h2 class="mb-0" id="flush-headingOne hidden">
              <button
                @click="openClose(i)"
                  class="group relative flex w-full items-center rounded-none border-0 !bg-gradient-to-r from-dark-300 to-dark-100 px-5 py-1  text-left text-base font-medium  text-white transition [overflow-anchor:none] hover:z-[2] focus:z-[3] focus:outline-none dark:bg-neutral-800 dark:text-white [&:not([data-te-collapse-collapsed])]:bg-white [&:not([data-te-collapse-collapsed])]:text-primary [&:not([data-te-collapse-collapsed])]:[box-shadow:inset_0_-1px_0_rgba(229,231,235)] dark:[&:not([data-te-collapse-collapsed])]:bg-neutral-800 dark:[&:not([data-te-collapse-collapsed])]:text-primary-400 dark:[&:not([data-te-collapse-collapsed])]:[box-shadow:inset_0_-1px_0_rgba(75,85,99)]"
                type="button">
                {{ item.name }}
                <span
                  class="-mr-1 ml-auto h-5 w-5 shrink-0 rotate-[-180deg] fill-[#336dec] transition-transform duration-200 ease-in-out group-[[data-te-collapse-collapsed]]:mr-0 group-[[data-te-collapse-collapsed]]:rotate-0 group-[[data-te-collapse-collapsed]]:fill-[#212529] motion-reduce:transition-none dark:fill-blue-300 dark:group-[[data-te-collapse-collapsed]]:fill-white">
                  <Icon :fa="!item.active ? 'arrow-down-wide-short' : 'times'">
                </span>
              </button>
            </h2>
            <div
              class="!visible border-0"
              :class="item.active ? '' : 'hidden'">
              <div class="">
                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                  <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                Nomor Approval
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Nomor Transaksi
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Pemohon
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Tgl Transaksi
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Tgl Pengajuan Approval
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                      <tr 
                        v-for="d, idx in item.data" :key="idx" 
                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 cursor-pointer" 
                        title="klik untuk membuka detail approval"
                        @click="detail(i,idx)"
                      >
                        <th scope="row" class="px-6 py-2 font-medium text-blue-700 whitespace-nowrap dark:text-white">
                            {{ d.nomor }}
                        </th>
                        <th scope="row" class="px-6 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ d.trx_nomor }}
                        </th>
                        <td class="px-6 py-2">
                            {{ d.creator }}
                        </td>
                        <td class="px-6 py-2">
                            {{ d.trx_date }}
                        </td>
                        <td class="px-6 py-2">
                            {{ d.created_at }}
                        </td>
                        <td class="px-6 py-2">
                            {{ d.status }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- <TableApi class="w-[100%] rounded-b" ref='apiTable' :api="landing.api" :columns="landing.columns"
                  :actions="landing.actions">
                  <template #header>
                  </template>
                </TableApi> -->
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  
      

  </div>

</div>
@endverbatim
