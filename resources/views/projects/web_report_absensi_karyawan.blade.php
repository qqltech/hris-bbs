<<<<<<< HEAD
@php
  $req = app()->request;
  $tipe = $req->tipe_report;
  $kary = $req->kary_id;


  $rekap = [];
  if($tipe === 'Rekap'){
    $periode_from = $req->periode_from;
    $periode_to = $req->periode_to;
    $tanggal_awal = new DateTime($periode_from);
    $tanggal_akhir = new DateTime($periode_to);
    $range_bulan = [];
    while ($tanggal_awal <= $tanggal_akhir) {
        $range_bulan[] = $tanggal_awal->format('Y-m');
        $tanggal_awal->modify('+1 month');
    }
    for($i = 0 ; $i< count($range_bulan); $i++){
        $temp = $i +1;
        $rekap[] = \DB::select("
                          select 
                            all_days_of_month ,
                            date_to_idn,
                            day_name_idn,
                            'type', type,
                            'presentase', presentase,
                            'attend', attend,
                            'cuti', cuti,
                            'alpha', alpha,
                            employee_attendance(all_days_of_month,?) absen
                    from generate_monthly_report(?)
      ", [$kary, $range_bulan[$i].'-01']);
    }
  }
  if($tipe === 'Detail')
  {
      $periode = $req->periode;
      $periodeArray = explode('-', $periode);
      $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $periodeArray[1], $periodeArray[0]);
      for ($day = 1; $day <= $daysInMonth; $day++) {
          $rekap[] = \DB::select("
              SELECT json_agg(json_build_object(
                  'm_kary_id', m_kary_id,
                  'default_user_id', default_user_id,
                  'kode', kode,
                  'nama_lengkap', nama_lengkap,
                  'dept', dept,
                  'absensi', absensi
              )) AS att_report
              FROM get_employee_attendance_report(?,?,?)
              WHERE m_kary_id = ? 
          ", [ $periode . '-' . sprintf("%02d", $day), $req->m_divisi_id, $req->m_dept_id, $kary ]);
      }
  }
@endphp
<span style="width:100%;text-align:center;font-weight:bold;"> Absensi Karyawan </span><br/>
<style>

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1rem;
  font-size: 8px;
}

th {
  border: 1px solid #4A5568;
}

td {
  border: 1px solid #4A5568;
}

td .cursor-pointer {
  cursor: pointer;
}

td.text-right {
  text-align: right;
}

</style>

<br/>
  @if($tipe === 'Rekap')
    <table v-else class="table-auto w-full">
      <thead class="bg-[#c6c6c6]">
        <tr>
          <th class="border-1 border-gray-500 px-3 py-1" style="background-color: #c6c6c6;">Hari</th>
          <th class="border-1 border-gray-500 px-3 py-1" style="background-color: #c6c6c6;">Tanggal</th>
          <th class="border-1 border-gray-500 px-3 py-1" style="background-color: #c6c6c6;">TIpe</th>
        </tr>
      </thead>
      <tbody>
        @foreach($rekap as $rekaps)
          @foreach($rekaps as $detRekap)
              @php 
                $bg_class = $detRekap->type == 'Hari Libur' ? 'bg-gray-500 text-white' : ( $detRekap->type == 'Cuti Bersama' ? 'bg-red-200' : '');
              @endphp
                <tr class="{{ $bg_class }}">
                    <td class="text-left border-1 border-gray-500 px-3">{{ $detRekap->day_name_idn }}</td>
                    <td class="text-left border-1 border-gray-500 px-3">{{ $detRekap->all_days_of_month }}</td>
                    <td class="text-right border-1 border-gray-500 px-3">{{ $detRekap->type }}</td>
                </tr>
          @endforeach
        @endforeach
      </tbody>
    </table>
  @elseif($tipe === 'Detail')
    @foreach($rekap as $key1 => $rekaps)
      @foreach($rekaps as $detRekap)
          <span style="width:100%;text-align:center;font-weight:bold;"> {{$periode . '-' . sprintf("%02d", ($key1+1))}} </span><br/>
        @php
            dd($rekap);
            $data = json_decode($detRekap->att_report);
        @endphp
         
          
          <table class="table-auto w-full">
            <thead class="bg-[#c6c6c6]">
              <tr>
                <th class="border-1 border-gray-500 px-3 py-1 w-[5%]" style="background-color: #c6c6c6;">No</th>
                <th class="border-1 border-gray-500 px-3 py-1 w-[10%]" style="background-color: #c6c6c6;">NIK</th>
                <th class="border-1 border-gray-500 px-3 py-1 w-[25%]" style="background-color: #c6c6c6;">Nama</th>
                <th class="border-1 border-gray-500 px-3 py-1 w-[27%]" style="background-color: #c6c6c6;">Departemen</th>
                <th class="border-1 border-gray-500 px-3 py-1 w-[10%]" style="background-color: #c6c6c6;">Status</th>
                <th class="border-1 border-gray-500 px-3 py-1 w-[12%]" style="background-color: #c6c6c6;">Waktu Checkin</th>
                <th class="border-1 border-gray-500 px-3 py-1 w-[12%]" style="background-color: #c6c6c6;">Lokasi Checkin</th>
                <th class="border-1 border-gray-500 px-3 py-1 w-[12%]" style="background-color: #c6c6c6;">Onscope Checkin</th>
                <th class="border-1 border-gray-500 px-3 py-1 w-[12%]" style="background-color: #c6c6c6;">Waktu Checkout</th>
                <th class="border-1 border-gray-500 px-3 py-1 w-[12%]" style="background-color: #c6c6c6;">Lokasi Checkout</th>
                <th class="border-1 border-gray-500 px-3 py-1 w-[12%]" style="background-color: #c6c6c6;">Onscope Checkout</th>
              </tr>
            </thead>
            <tbody>
                @foreach($data as $key => $datas)
                @php 
                  $bg_class = $datas->absensi->status == 'NOT ATTEND' ? 'bg-red-200' : ($key % 2 === 0 ? 'bg-gray-200' :'');
                @endphp
                   <tr class="{{ $bg_class }}">
                        <td class="text-left border-1 border-gray-500 px-3">{{ $key+1 }}</td>
                        <td class="text-left border-1 border-gray-500 px-3">{{ $datas->kode }}</td>
                        <td class="text-left border-1 border-gray-500 px-3">{{ $datas->nama_lengkap }}</td>
                        <td class="text-left border-1 border-gray-500 px-3">{{ $datas->dept }}</td>
                        <td class="text-left border-1 border-gray-500 px-3">{{ $datas->absensi->status }}</td>
                        <td class="text-left border-1 border-gray-500 px-3">
                            <div class="flex items-center">
                                <icon  class="mx-1" class="" fa="map-marker-alt"/> {{ optional($datas->absensi)->checkin_time ?: '' }}
                            </div>
                        </td>
                        <td class="text-left border-1 border-gray-500 px-3"> Lat :{{ optional($datas->absensi)->checkin_lat ?: '' }} <br> Long : {{ optional($datas->absensi)->checkin_long ?: '' }}</td>
                        <td class="text-left border-1 border-gray-500 px-3">
                          @if(isset($datas->absensi->checkin_region))
                              {{ $datas->absensi->checkin_region === 'Out Scope' ? 'Tidak' : 'Iya' }}
                          @endif
                        </td>
                        <td class="text-left border-1 border-gray-500 px-3">
                            <div class="flex items-center">
                                <icon class="mx-1" class="" fa="map-marker-alt"/> {{ optional($datas->absensi)->checkout_time ?: '' }}
                            </div>
                        </td>
                        <td class="text-left border-1 border-gray-500 px-3"> Lat :{{ optional($datas->absensi)->checkout_lat ?: '' }} <br> Long : {{ optional($datas->absensi)->checkout_long ?: '' }}</td>
                        <td class="text-left border-1 border-gray-500 px-3">
                          @if(isset($datas->absensi->checkout_region))
                              {{ $datas->absensi->checkout_region === 'Out Scope' ? 'Tidak' : 'Iya' }}
                          @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
          </table>

      @endforeach
    @endforeach
   
  @else

  @endif
=======
<?php
 // blade php file
>>>>>>> 9801edb8aa1b87cbdd96c45a13d5a6acdabdcbc0
