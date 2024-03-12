//   javascriptimport { useRouter, useRoute, RouterLink } from 'vue-router'
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
const detailArrKaryawan = ref([])

// ------------------------------ PERSIAPAN
const endpointApi = '/presensi_maksi_jadwal_ambil'
onBeforeMount(()=>{
  document.title = 'Form Jadwal Pengambilan Makan Siang'
})

let initialValues = {}
const changedValues = []
let resultValues = reactive({items:[]})

const values = reactive({
})

function onDetailAdd(selectedData) {
  console.log("test")
  console.log(selectedData)
  console.log(detailArrKaryawan.items)
  console.log(detailArrKaryawan.value)
  
  // const newData = {
  //   id: selectedData.id,
  //   nik: selectedData.nik || '', // Handle jika nik null
  //   nama: selectedData.nama_lengkap || '', // Gunakan nama_lengkap jika ada, jika tidak, gunakan string kosong
  //   m_dept_nama: selectedData['m_dept.nama'] || '', // Handle jika 'm_dept.nama' null
  // };

  // console.log(newData)

  detailArrKaryawan.value.push(selectedData);
  // detailArrKaryawan.items = selectedData;
}

watchEffect(()=>store.commit('set', ['isRequesting', isRequesting.value]))