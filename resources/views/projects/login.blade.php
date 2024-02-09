<div class="overflow-hidden">
  @verbatim
  <div class="grid grid-cols-2 <md:grid-cols-1">
    <div class="h-screen bg-[#FCFCFC] w-full flex items-center justify-center <md:hidden">
      <img src="https://server.qqltech.com:7005/uploads/m_file/1218523680954481:::7778903_3754379-removebg-preview 1.svg" class="w-[43.75rem] h-[29.16669rem]">
    </div>
    <form @submit="onLogin" class="flex flex-col h-[100%] space-y-6 bg-white px-[126px] justify-center w-full <md:h-screen px-[56px]">
      <img src="https://server.qqltech.com:7005/uploads/m_file/0441131821255568:::Logo Success Jaya.png" class="w-[50%] mb-8">
      <h2 class="text-[22px] md:text-[28px] font-700">Welcome Back</h2>
      <h3 class="text-[16px] text-[#8F8F8F]">Silahkan masuk ke akun anda</h3>
        <FieldX :bind="{ readonly: false }"
          :value="values.email"
          @input="v=>values.email=v"
          placeholder="Username" fa-icon="user" :check="false"
        />
        <FieldX :bind="{ readonly: false }" type="password"
          :value="values.password" 
          @input="v=>values.password=v" 
          placeholder="Password" fa-icon="lock" :check="false"
        />
      <button type="submit" class="bg-[#387CEE] text-white py-[11px] rounded-[10px]">
        Masuk
      </button>
  </form>
  </div>
  @endverbatim
</div>
