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
const endpointApi = '/m_kary'
onBeforeMount(()=>{
  document.title = 'Adjusment Cuti'
})

let initialValues = {}
const changedValues = []

const values = reactive({
})

onBeforeMount(async () => {
  
    isRequesting.value = true
    //  READ DATA
  try {
    const editedId = route.params.id
    const dataURL = `${store.server.url_backend}/operation${endpointApi}/${editedId}`
    
    const params = { join: true, transform: false, detail:true }
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
    if(initialValues.info_cuti){
        for (let key in initialValues.info_cuti) {
            if (initialValues.info_cuti.hasOwnProperty(key) && initialValues.info_cuti[key] === null) {
                initialValues.info_cuti[key] = 0;
            }
        }
      }
      initialValues = {...initialValues, ...initialValues.info_cuti}
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

  for (const key in initialValues) {
    values[key] = initialValues[key]
  }
})


function onBack() {
  let isChanged = false
  for (const key in initialValues) {
    if (values[key] !== initialValues[key]) {
      isChanged = true
      break;
    }
  }
      router.replace('/m_karyawan')
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
  //values.tags = JSON.stringify(values.tags)
      try {
        const isCreating = ['Create','Copy','Tambah'].includes(actionText.value)
        const dataURL = `${store.server.url_backend}/operation${endpointApi}${isCreating ? '' : ('/' + route.params.id)}`
        isRequesting.value = true
         values.is_active = values.is_active ? 1 : 0
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
