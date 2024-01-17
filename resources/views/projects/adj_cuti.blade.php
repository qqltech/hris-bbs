@verbatim

<div class="flex flex-col gap-y-3">
  <div class="flex gap-x-4 px-2">
    <div class="flex flex-col border rounded-2xl shadow-sm px-6 py-6 <md:w-full w-full bg-white">

      <!-- HEADER START -->
      <div class="flex items-center justify-between mb-2 pb-4">
        <h2 class="font-sans text-xl flex justify-left font-bold">
          Adjusment Cuti
        </h2>
      
      </div>
      <!-- HEADER END -->

      <!-- FORM START -->
      <div class="grid <md:grid-cols-1 grid-cols-2 grid-flow-row gap-x-4 gap-y-4 mb-5">
        <div class="col-span-8 md:col-span-6">
            <div class="grid grid-cols-12 items-center gap-2 ">
              <div class="grid grid-cols-12 col-span-6 gap-y-2">
                <label class="col-span-12">Jatah Cuti Tahunan<label class="text-red-500 space-x-0 pl-0"></label></label>
                <FieldX :bind="{ readonly: false }" type="number" class="col-span-12 !mt-0 w-full"
                  :value="values.cuti_reguler" label="" placeholder="Tuliskan Jatah Cuti Reguler" :errorText="formErrors.cuti_reguler?'failed':''"
                  @input="v=>values.cuti_reguler=v" :hints="formErrors.cuti_reguler" :check="false"
                />
              </div>
              <div class="grid grid-cols-12 col-span-6 gap-y-2">
                <label class="col-span-12">Sisa Cuti Tahunan<label class="text-red-500 space-x-0 pl-0"></label></label>
                <FieldX :bind="{ readonly: false }" type="number" class="col-span-12 !mt-0 w-full"
                  :value="values.sisa_cuti_reguler" label="" placeholder="Tuliskan Sisa Jatah Cuti Reguler" :errorText="formErrors.sisa_cuti_reguler?'failed':''"
                  @input="v=>values.sisa_cuti_reguler=v" :hints="formErrors.sisa_cuti_reguler" :check="false"
                />
              </div>
            </div>
          </div>
          <div class="col-span-8 md:col-span-6">
            <div class="grid grid-cols-12 items-center gap-2 ">
              <div class="grid grid-cols-12 col-span-6 gap-y-2">
                <label class="col-span-12">Jatah Cuti Masa Kerja<label class="text-red-500 space-x-0 pl-0"></label></label>
                <FieldX :bind="{ readonly: false }" type="number" class="col-span-12 !mt-0 w-full"
                  :value="values.cuti_masa_kerja" label="" :errorText="formErrors.cuti_masa_kerja?'failed':''"
                  @input="v=>values.cuti_masa_kerja=v" :hints="formErrors.cuti_masa_kerja" :check="false"
                />
              </div>
              <div class="grid grid-cols-12 col-span-6 gap-y-2">
                <label class="col-span-12">Sisa Cuti Masa Kerja<label class="text-red-500 space-x-0 pl-0"></label></label>
                <FieldX :bind="{ readonly: false }" type="number" class="col-span-12 !mt-0 w-full"
                  :value="values.sisa_cuti_masa_kerja" label="" :errorText="formErrors.sisa_cuti_masa_kerja?'failed':''"
                  @input="v=>values.sisa_cuti_masa_kerja=v" :hints="formErrors.sisa_cuti_masa_kerja" :check="false"
                />
              </div>
            </div>
          </div>
          <div class="col-span-8 md:col-span-6">
            <div class="grid grid-cols-12 items-center gap-2 ">
              <div class="grid grid-cols-12 col-span-6 gap-y-2">
                <label class="col-span-12">Jatah Cuti P24<label class="text-red-500 space-x-0 pl-0"></label></label>
                <FieldX :bind="{ readonly: false }" type="number" class="col-span-12 !mt-0 w-full"
                  :value="values.cuti_p24" label="" :errorText="formErrors.cuti_p24?'failed':''"
                  @input="v=>values.cuti_p24=v" :hints="formErrors.cuti_p24" :check="false"
                />
              </div>
              <div class="grid grid-cols-12 col-span-6 gap-y-2">
                <label class="col-span-12">Sisa Cuti P24<label class="text-red-500 space-x-0 pl-0"></label></label>
                <FieldX :bind="{ readonly: false }" type="number" class="col-span-12 !mt-0 w-full"
                  :value="values.cuti_p24_terpakai" label="" :errorText="formErrors.cuti_p24_terpakai?'failed':''"
                  @input="v=>values.cuti_p24_terpakai=v" :hints="formErrors.cuti_p24_terpakai" :check="false"
                />
              </div>
            </div>
          </div>    
      </div>

      <div class="flex justify-end mb-4 gap-4">
        <button @click="onBack" class="bg-[#EF4444] hover:bg-[#ed3232] text-white px-[36.5px] py-[12px] rounded-[6px] w-32">
            Batal
        </button>
        <button v-show="actionText" @click="onSave" class="bg-[#10B981] hover:bg-[#0ea774] text-white px-[36.5px] py-[12px] rounded-[6px] w-32">
            Simpan
        </button>
      </div>


      <!-- FORM END -->

    </div>
  </div>

    
</div>

@endverbatim