import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, watchEffect, onActivated } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')

const isRead = route.params.id && route.params.id !== 'create'
const actionText = ref(route.params.id === 'create' ? 'Tambah' : (route.query.action?.toLowerCase() === 'verifikasi' ? null : route.query.action))
const isBadForm = ref(false)
const isRequesting = ref(false)
const modulPath = route.params.modul
const currentMenu = store.currentMenu
const apiTable = ref(null)
const formErrors = ref({})
const tsId = `ts=`+(Date.parse(new Date()))

// ------------------------------ PERSIAPAN
const endpointApi = '/t_rpd'
onBeforeMount(()=>{
  document.title = 'Realisasi Perjalanan Dinas'
})

const formatCurrency = (text) => {
  if (!text) text = 0

    const formatter = new Intl.NumberFormat('id', {
      style: 'currency',
      currency: 'IDR',
      maximumFractionDigits: 0,
    })

    if (typeof text === 'string') {
      if (isNaN(parseFloat(text)) || isNaN(parseInt(text))) {
        return formatter.format(0)
      }

      if (text.includes(',') || text.includes('.')) {
        return formatter.format(parseFloat(text))
      }

      return formatter.format(parseInt(text))
    }

    return formatter.format(text)
}
//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS
let initialValues = {}
const changedValues = []

const values = reactive({
  // direktorat: store.user.data?.direktorat
})

onBeforeMount(async () => {
  if (isRead) {
    //  READ DATA
    try {
      const editedId = route.params.id
      const dataURL = `${store.server.url_backend}/operation${endpointApi}/${editedId}`
      isRequesting.value = true

      const params = { join: true, transform: false }
      const fixedParams = new URLSearchParams(params)
      const res = await fetch(dataURL + '?' + fixedParams, {
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
      })
      if (!res.ok) throw new Error("Failed when trying to read data")
      const resultJson = await res.json()
      initialValues = resultJson.data
      if(initialValues.t_spd_id){
        const dataURL2 = `${store.server.url_backend}/operation/t_spd/${initialValues.t_spd_id}`
        const res2 = await fetch(dataURL2, {
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          },
        })
        if (!res2.ok) throw new Error("Failed when trying to read data")
        const resultJson2 = await res2.json()
        const initialValues2 = resultJson2.data
        values.direktorat = initialValues2['m_dir.nama']
        values.divisi = initialValues2['m_divisi.nama']
        values.departemen = initialValues2['m_dept.nama']
        values.posisi = initialValues2['m_posisi.desc_kerja']
        values.tanggal = initialValues2.tanggal
        values.tgl_awal = initialValues2.tgl_acara_awal
        values.tgl_akhir = initialValues2.tgl_acara_akhir
        values.jml_hari = initialValues2.tgl_acara_akhir.split('/')[0] - initialValues2.tgl_acara_awal.split('/')[0]
        values.zona_asal = initialValues2['m_zona_asal.nama']
        values.zona_tujuan = initialValues2['m_zona_tujuan.nama']
        values.lokasi_tujuan = initialValues2['m_lokasi_tujuan.nama']
        values.nik = initialValues2['m_kary.nik']
        values.pic = initialValues2['pic.nama_lengkap']
        values.total_biaya_spd = initialValues2.total_biaya
        values.is_kend_dinas = initialValues2.is_kend_dinas
      }
      initialValues.t_rpd_det?.forEach((items)=>{
        items.__id = ++_id
        items.isDisabled = true
        items.is_knd_dinas = true
        detailArr.value = [items, ...detailArr.value]
      })
      // initialValues.status=(initialValues.status.toUpperCase()==='POSTED')?true:false
    } catch (err) {
      isBadForm.value = true
      swal.fire({
        icon: 'error',
        text: err,
        allowOutsideClick: false,
        confirmButtonText: 'Kembali',
      }).then(() => {
        router.back()
      })
    }
    isRequesting.value = false
  }

  for (const key in initialValues) {
    values[key] = initialValues[key]
  }
})

let _id = 0
const detailArr = ref([])
const addDetail = () => {
  const tempItem = {
    __id : ++_id,
    t_spd_det_id: values.t_spd_id??0
  }
  detailArr.value = [...detailArr.value, tempItem]
}

const getDetailSPD = async () => {
  try{
    detailArr.value = []
    isRequesting.value = true
    const res3 = await fetch(`${store.server.url_backend}/operation/t_spd/${values.t_spd_id}`, {
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
    })
    if (!res3.ok) throw ("Gagal Mendapatkan Data Detail SPD")
    const resultJson3 = await res3.json()
    // console.log(resultJson3)
    resultJson3.data.t_spd_det?.forEach(async (item) => {
      // console.log('cok',item)
      const tempObItem = {
        __id: ++_id,
        t_spd_det_id: values.t_spd_id,
        tipe_spd_id: item.tipe_spd_id,
        biaya: item.biaya,
        keterangan: item.keterangan,
        // is_kendaraan_dinas: item.is_kendaraan_dinas,
        biaya_realisasi: null,
        // m_knd_dinas_id: item.m_knd_dinas_id ?? null,
        isDisabled: true
        // is_knd_dinas: item.is_kendaraan_dinas?true:false
      }
      detailArr.value = [tempObItem, ...detailArr.value]
    })
  }catch(err){
      isBadForm.value = true
      swal.fire({
        icon: 'error',
        text: err,
      })
    }
    isRequesting.value = false
  
}

const countBiayaSelisih = () =>{
  const total = detailArr.value.reduce((acm, item) => {
    // console.log(item.biaya_realisasi)
    return acm + item.biaya_realisasi;
  }, 0);
  values.total_biaya_selisih = values.total_biaya_spd - total
}

const removeDetail = (detailItem) => {
  values.total_biaya_selisih -= detailItem.biaya_realisasi
  detailArr.value = detailArr.value.filter((e) => e.__id != detailItem.__id)
}

function onBack() {
  let isChanged = false
  for (const key in initialValues) {
    if (values[key] !== initialValues[key]) {
      isChanged = true
      break;
    }
  }

  if (!isChanged) {
    router.replace('/' + modulPath)
    return
  }

      router.replace('/' + modulPath)
    
}

async function posted() {
  const payload = {
    id: route.params.id
  }
  try {
    const dataURL = `${store.server.url_backend}/operation${endpointApi}/postData`
    isRequesting.value = true
    const res = await fetch(dataURL, {
      method: 'POST',
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      body: JSON.stringify(payload)
    })
    if (!res.ok) {
      if ([400, 422].includes(res.status)) {
        const responseJson = await res.json()
        formErrors.value = responseJson.errors || {}
        throw (responseJson.message || "Failed when trying to post data")
      } else {
        throw ("Failed when trying to post data")
      }
    }
    router.replace('/' + modulPath + '?reload='+(Date.parse(new Date())))
  } catch (err) {
    isBadForm.value = true
    swal.fire({
      icon: 'error',
      text: err
    })
  }
  isRequesting.value = false
  
}

function onReset() {
  swal.fire({
    icon: 'warning',
    text: 'Reset this form data?',
    showDenyButton: true
  }).then((res) => {
    if (res.isConfirmed) {
      for (const key in initialValues) {
        values[key] = initialValues[key]
      }
    }
  })
}

async function onSave() {
  try {
    // values.status=(values.status===true)?'POSTED':'DRAFT'
    detailArr.value.forEach((items)=>{
      items.is_kendaraan_dinas = items.is_kendaraan_dinas?1:0
    })
    values.t_rpd_det = detailArr.value
    const isCreating = ['Create','Copy','Tambah'].includes(actionText.value)
    const dataURL = `${store.server.url_backend}/operation${endpointApi}${isCreating ? '' : ('/' + route.params.id)}`
    isRequesting.value = true
    const res = await fetch(dataURL, {
      method: isCreating ? 'POST' : 'PUT',
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      body: JSON.stringify(values)
    })
    if (!res.ok) {
      if ([400, 422].includes(res.status)) {
        const responseJson = await res.json()
        formErrors.value = responseJson.errors || {}
        throw (responseJson.errors.length ? responseJson.errors[0] : responseJson.message || "Failed when trying to post data")
      } else {
        throw ("Failed when trying to post data")
      }
    }
    router.replace('/' + modulPath + '?reload='+(Date.parse(new Date())))
  } catch (err) {
    isBadForm.value = true
    swal.fire({
      icon: 'error',
      text: err
    })
  }
  isRequesting.value = false

}

//  @else----------------------- LANDING
const landing = reactive({
  actions: [
    {
      icon: 'trash',
      class: 'bg-red-600 text-light-100',
      title: "Hapus",
      show: (row) => row.status?.toUpperCase() !== 'POSTED',
      click(row) {
        swal.fire({
          icon: 'warning',
          text: 'Hapus Data Terpilih?',
          confirmButtonText: 'Yes',
          showDenyButton: true,
        }).then(async (result) => {
          if (result.isConfirmed) {
            try {
              const dataURL = `${store.server.url_backend}/operation${endpointApi}/${row.id}`
              isRequesting.value = true
              const res = await fetch(dataURL, {
                method: 'DELETE',
                headers: {
                  'Content-Type': 'Application/json',
                  Authorization: `${store.user.token_type} ${store.user.token}`
                }
              })
              if (!res.ok){ 
                const resultJson = await res.json()
                throw (resultJson.message || "Failed when trying to remove data")
              }
              apiTable.value.reload()
              // const resultJson = await res.json()
            } catch (err) {
              isBadForm.value = true
              swal.fire({
                icon: 'error',
                text: err
              })
            }
            isRequesting.value = false
          }
        })
      }
    },
    {
      icon: 'eye',
      title: "Read",
      class: 'bg-green-600 text-light-100',
      // show: (row) => (currentMenu?.can_read)||store.user.data.username==='developer',
      click(row) {
        router.push(`${route.path}/${row.id}?`+tsId)
      }
    },
    {
      icon: 'edit',
      title: "Edit",
      class: 'bg-blue-600 text-light-100',
      show: (row) => row.status?.toUpperCase() !== 'POSTED',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Edit&`+tsId)
      }
    },
    {
      icon: 'copy',
      title: "Copy",
      class: 'bg-gray-600 text-light-100',
      show: (row) => row.status?.toUpperCase() !== 'POSTED',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Copy&`+tsId)
      }
    },
    {
      icon: 'paper-plane',
      title: "Posted Data",
      class: 'bg-rose-700 rounded-lg text-white',
      show: (row) =>row.status?.toUpperCase() === 'DRAFT',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Verifikasi&`+tsId)
      }
    }
  ],
  api: {
    url: `${store.server.url_backend}/operation${endpointApi}`,
    headers: {
      'Content-Type': 'Application/json',
      authorization: `${store.user.token_type} ${store.user.token}`
    },
    params: {
      simplest: true,
      join: true,
      searchfield:'this.id, this.nomor, t_spd.nomor, t_spd.tanggal, t_spd.tgl_acara_awal, t_spd.tgl_acara_akhir, this.total_biaya_spd, this.total_biaya_selisih, this.status',
    },
    onsuccess(response) {
      response.page = response.current_page
      response.hasNext = response.has_next
      return response
    }
  },
  columns: [{
    headerName: 'No',
    valueGetter: (params) => params.node.rowIndex + 1,
    width: 60,
    sortable: true,
    resizable: true,
    filter: true,
    cellClass: ['justify-center', 'bg-gray-50', 'border-r', '!border-gray-200']
  },
  {
    field: 'nomor',
    filter: true,
    sortable: true,
    flex:1,
    filter: 'ColFilter',
    resizable: true,
    wrapText: true,
    cellClass: [ 'border-r', '!border-gray-200', 'justify-start']
  },
  {
    field: 't_spd.nomor',
    headerName: 'Nomor SPD',
    filter: true,
    sortable: true,
    flex:1,
    filter: 'ColFilter',
    resizable: true,
    wrapText: true,
    cellClass: [ 'border-r', '!border-gray-200', 'justify-start']
  },
  {
    headerName: 'Tanggal',
    field: 't_spd.tanggal',
    filter: true,
    sortable: true,
    flex:1,
    filter: 'ColFilter',
    resizable: true,
    wrapText: true,
    cellClass: [ 'border-r', '!border-gray-200', 'justify-end']
  },
  {
    headerName: 'Tanggal Awal',
    field: 't_spd.tgl_acara_awal',
    filter: true,
    sortable: true,
    flex:1,
    filter: 'ColFilter',
    resizable: true,
    wrapText: true,
    cellClass: [ 'border-r', '!border-gray-200', 'justify-end']
  },
  {
    headerName: 'Tanggal Akhir',
    field: 't_spd.tgl_acara_akhir',
    filter: true,
    sortable: true,
    flex:1,
    filter: 'ColFilter',
    resizable: true,
    wrapText: true,
    cellClass: [ 'border-r', '!border-gray-200', 'justify-end']
  },
  {
    headerName: 'Besaran',
    cellRenderer: ( params ) => {
      return formatCurrency(params.data.total_biaya_spd)
    },
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex:1,
    wrapText: true,
    cellClass: [ 'border-r', '!border-gray-200', 'justify-end']
  },
  {
    headerName: 'Besaran',
    cellRenderer: ( params ) => {
      return formatCurrency(params.data.total_biaya_selisih)
    },
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex:1,
    wrapText: true,
    cellClass: [ 'border-r', '!border-gray-200', 'justify-end']
  },
  {
    field: 'status',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex:1,
    wrapText: true,
    cellClass: [ 'border-r', '!border-gray-200', 'justify-start'],
  },
  ]
})

onActivated(() => {
  //  reload table api landing
  if (apiTable.value) {
    if (route.query.reload) {
      apiTable.value.reload()
    }
  }
})

//  @endif -------------------------------------------------END
watchEffect(()=>store.commit('set', ['isRequesting', isRequesting.value]))