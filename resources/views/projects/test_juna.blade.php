@if(!$req->has('id'))

@verbatim
<div class="bg-white p-6 rounded-xl flex justify-center flex-col">
  <h1 class="font-semibold text-xl text-center">{{form.attending?.toLowerCase() === 'not attend' ? 'Presensi Checkin' : (form.attending?.toLowerCase() === 'working' ? 'Presensi Checkout' : 'Sudah Absen')}} </h1>
    <div class="mt-4 lg:mt-6">
      <video v-show="!isImage" v-if="form.attending?.toLowerCase() !== 'attend'" ref="videoElement" autoplay playsinline muted class="rounded-xl h-full lg:h-[20rem] m-auto"></video>
      <!-- <div v-if="isImage" class="bg-gray-600 rounded-xl"></div> -->
      <div v-else class="bg-gray-700 m-auto rounded-xl w-[426px] h-[320px]"></div>
      <img v-show="isImage" id="imgElem" class="max-w-[426px] max-h-[320px] m-auto rounded-xl"></img>
    </div>
    <div class="flex mt-4 justify-center space-x-4 lg:mt-6">
      <div v-show="form.attending?.toLowerCase() !== 'attend'">
        <button v-show="!isImage" @click="capture" class="bg-blue-600 hover:bg-blue-700 text-white w-fit px-6 py-2 rounded-lg m-auto">Capture</button>
        <button v-show="isImage" @click="recapture" class="bg-yellow-600 hover:bg-yellow-700 text-white w-fit px-6 py-2 rounded-lg m-auto">Recapture</button>
      </div>
      <div v-show="form.attending?.toLowerCase() !== 'attend'">
        <button @click="postAttend" :disabled="!isImage" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">Absen {{form.attending?.toLowerCase() === 'not attend' ? 'Checkin' :'Checkout'}}</button>
      </div>
      <div v-show="form.attending?.toLowerCase() === 'attend'">
        <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">Lihat Detail Absen</button>
      </div>
    </div>
    <div class="flex justify-between mt-6">
      <div class="flex space-x-2 items-center">
        <icon fa="calendar" class="text-blue-600"/>
        <h2 class="text-md lg:pr-10 text-gray-700">{{form.day}}, {{form.tanggal}}</h2>
      </div>
      <div class="flex space-x-2 items-center">
        <icon fa="clock" class="text-blue-600"/>
        <h2 class="text-md lg:pr-10 text-gray-700">{{form.currentTime}}</h2>
      </div>
  </div>
  <hr>
  <div class="px-4">
    <table class="mt-2 lg:block hidden">
      <tr>
        <td class="align-top font-semibold">Lokasi</td>
        <td class="px-2 align-top font-semibold">:</td>
        <td class="align-top pb-2">{{form.address}}</td>
      </tr>
      <tr>
        <td class="align-top font-semibold">Keterangan</td>
        <td class="px-2 align-top font-semibold">:</td>
        <td :class="form.distance_check ? 'text-green-600 align-top':'text-red-600 align-top'">{{form.distance_check ? 'On Scope' : 'Out Scope'}}</td>
      </tr>
    </table>
    <table class="mt-4 block lg:hidden">
      <tr>
        <td colspan="2" class="align-top font-semibold">Lokasi :</td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td class="align-top pb-6">{{form.address}}</td>
      </tr>
      <tr>
        <td colspan="2" class="align-top font-semibold">Keterangan :</td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td :class="form.distance_check ? 'text-green-600 align-top':'text-red-600 align-top'">{{form.distance_check ? 'On Scope' : 'Out Scope'}}</td>
      </tr>
    </table>
  </div>
  <!-- <img v-if="capturedImage" :src="capturedImage" alt="Captured Image"> -->
</div>
@endverbatim
@else


@verbatim

<div>
  <div class="flex flex-col bg-white p-6 w-full h-full">
    <Writer :value="values.content" 
    @input="$log('halo')" />
  </div>
</div>
@endverbatim
@endif