//   javascript//   javascript

import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, watchEffect, onActivated } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')

const isRead = route.params.id && route.params.id !== 'create'
const actionText = ref(route.params.id === 'create' ? 'Tambah' : route.query.action)
const isBadForm = ref(false)
const isRequesting = ref(false)
const modulPath = route.params.modul
const currentMenu = store.currentMenu
const apiTable = ref(null)
const formErrors = ref({})
const tsId = `ts=`+(Date.parse(new Date()))

// ------------------------------ PERSIAPAN
const endpointApi = '/t_jadwal_kerja'
onBeforeMount(()=>{
  document.title = 'Jadwal Kerja'
})

//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS
let initialValues = {}
const changedValues = []
const modal = ref(false)
const groupKerja = ref([])
let values = reactive({
  m_comp_id : 1,
  nomor:'',
  group_kerja: '',
})



const onOpenGK = () => {
  modal.value = !modal.value
}

const selectGK = (data) => {
  values.m_comp_id = data.m_comp_id
  values.group_kerja = data.nomor
  values.m_dir = data["m_dir.nama"]
  values.m_dept = data["m_dept.nama"]
  values.m_divisi = data["m_divisi.nama"]
  values.m_dir_id = data["m_dir.id"]
  values.m_dept_id = data["m_dept.id"]
  values.m_divisi_id = data["m_dir.id"]
  values.t_grup_kerja_id = data.id
  modal.value = false
  groupKerja.selected = true
  }
  
  const deleteJKD = (index) => {
  
    const data = values.t_jadwal_kerja_det.filter((e, i) => {

     if(i !== index) return e
    })

    values.t_jadwal_kerja_det = data
  }

  const onGenerate = async () => {
    const res = await fetch(`${store.server.url_backend}/operation/t_jadwal_kerja/generate?grup_kerja_id=${values.t_grup_kerja_id}`,{  
    headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
      })

    const result = await res.json()
    console.log(result.data)
    values.t_jadwal_kerja_det = []
    values.t_jadwal_kerja_det = result.data
  }


const fetchGroupKerja = async () => {
  
      const params = { join: false, transform: false }
      const fixedParams = new URLSearchParams(params)
  const res = await fetch(`${store.server.url_backend}/operation/t_grup_kerja`,{  
    headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
      })

  const result = await res.json()
  const data = result.data
  console.log(data)
  groupKerja.value = data.map(item => {
    if(item.status){
      item.status = 'Active'
    }else {
      item.status = 'InActive'
    }
    return {
      ...item,
      selected: false
    }
  })


}

onBeforeMount( () => {
   fetchGroupKerja()
})

onBeforeMount(async () => {
  if (isRead) {
    //  READ DATA
    try {
      const editedId = route.params.id
      const dataURL = `${store.server.url_backend}/operation${endpointApi}/${editedId}`
      isRequesting.value = true

      const params = { join: false, transform: false }
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
  console.log(initialValues,'values awal')
  values.m_comp_id = initialValues.m_comp_id
  values.group_kerja = initialValues.nomor
  values.m_dir = initialValues["m_dir.nama"]
  values.m_dept = initialValues["m_dept.nama"]
  values.m_divisi = initialValues["m_divisi.nama"]
  values.m_dir_id = initialValues["m_dir.id"]
  values.m_dept_id = initialValues["m_dept.id"]
  values.m_divisi_id = initialValues["m_dir.id"]
  values.t_grup_kerja_id = initialValues.id
})



var bln = [];
var Bulan = [
  "Januari", "Februari", "Maret", "April", "Mei", "Juni",
  "Juli", "Agustus", "September", "Oktober", "November", "Desember"
];
for (let nama = 1; nama <= 12; nama++) {
  bln.push(Bulan[nama - 1]);
}

var periode = [];
for (let prd = 1; prd <= 500; prd++){
  periode.push(prd.toString())
}

var scp = [];
for (let tahun = 2000; tahun <= 2100; tahun++) {
  scp.push(tahun.toString());
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

  swal.fire({
    icon: 'warning',
    text: 'Buang semua perubahan dan kembali ke list data?',
    showDenyButton: true
  }).then((res) => {
    if (res.isConfirmed) {
      router.replace('/' + modulPath)
    }
  })
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

function onSave() {
  // values.tags = JSON.stringify(values.tags)
  swal.fire({
    icon: 'warning',
    text: 'Save data?',
    showDenyButton: true
  }).then(async (res) => {
    if (res.isConfirmed) {
      try {
        if(values.status){
          values.status = 'Active'
        }else {
          values.status = 'InActive'
        }
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
            throw new Error(responseJson.message || "Failed when trying to post data")
          } else {
            throw new Error("Failed when trying to post data")
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
  })
}

//  @else----------------------- LANDING
const landing = reactive({
  actions: [
    {
      icon: 'trash',
      class: 'bg-red-600 text-light-100',
      title: "Hapus",
      // show: () => store.user.data.username==='developer',
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
              if (!res.ok) throw new Error("Failed when trying to remove data")
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
      // show: (row) => (currentMenu?.can_update)||store.user.data.username==='developer',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Edit&`+tsId)
      }
    },
    {
      icon: 'copy',
      title: "Copy",
      class: 'bg-gray-600 text-light-100',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Copy&`+tsId)
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
      searchfield:`m_dir.nama, m_divisi.nama, m_dept.nama, t_grup_kerja.nomor, this.keterangan, this.is_active`
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
    headerName: 'Nomor',
    field: 'nomor',
    filter: true,
    sortable: true,
    flex:1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: [ 'border-r', '!border-gray-200', 'justify-center']
  },
  {
    headerName: 'Direktorat',
    field: 'm_dir.nama',
    filter: true,
    sortable: true,
    flex:1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: [ 'border-r', '!border-gray-200', 'justify-center']
  },
  {
    headerName: 'Divisi',
    field: 'm_divisi.nama',
    filter: true,
    sortable: true,
    flex:1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: [ 'border-r', '!border-gray-200', 'justify-center']
  },
  {
    headerName: 'Departement',
    field: 'm_dept.nama',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex:1,
    cellClass: [ 'border-r', '!border-gray-200', 'justify-center']
  },
  {
    headerName: 'Group Kerja',
    field: 't_grup_kerja.nomor',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex:1,
    cellClass: [ 'border-r', '!border-gray-200', 'justify-center']
  },
  {
    headerName: 'Keterangan',
    field: 'keterangan',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex:1,
    cellClass: [ 'border-r', '!border-gray-200', 'justify-center']
  }  ]
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