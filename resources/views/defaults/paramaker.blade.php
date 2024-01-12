<html>
<title>PREPARED QUERY</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="{{url('defaults/vue.min.js')}}"></script>
        <script src="{{url('defaults/axios.min.js')}}"></script>
<script src="https://unpkg.com/windicss-runtime-dom"></script>
<body class="block" hidden>
@verbatim
    <div class="w-screen min-h-screen !text-xs" id="app">
        <table class="w-full max-h-full overflow-y-auto px-2" cellpadding="0" cellspacing="0">
            <thead class="bg-gray-800 text-white !text-xs">
                <th class="p-2 border-r">Api Endpoint</th>
                <th class="p-2 border-r">Query Name</th>
                <th class="p-2 border-r">Keterangan</th>
                <th class="p-2 border-r">Prepared Query Statement</th>
                <th class="p-2 border-r">Active</th>
            </thead>
            <tbody>
                <tr v-for="(row,idx) in rows" class="border" :key="'row'+idx">
                    <td class="p-1 w-1/8 relative">
                        <input type='text' @focus="focusIdx=idx" @input="onInputChanged($event,row.id)" v-model="rows[idx].modul" class="w-full bg-gray-100 focus:bg-white border p-2">
                    </td>
                    <td class="p-1 w-1/8 relative">
                        <input type='text' @focus="focusIdx=idx" @input="onInputChanged($event,row.id)" v-model="rows[idx].name" class="w-full bg-gray-100 focus:bg-white border p-2">
                    </td>
                    <td class="p-1 w-1/4"><input type='text' v-model="rows[idx].note" @focus="focusIdx=idx" @input="onInputChanged($event,row.id)" class="w-full bg-gray-100 focus:bg-white border p-2"></td>
                    <td class="p-1">
                        <textarea 
                            v-model="rows[idx].prepared_query" style="resize:vertical;" @focus="focusIdx=idx" @input="onPDOInput($event,row.id)" 
                            class="min-w-full bg-gray-100 focus:bg-white border p-2 h-8.5 transition-all duration-300" 
                            :class="{'!h-40' : focusIdx===idx}"
                            rows="12">
                        </textarea>
                    </td>
                    <td class="p-1 w-60px text-center">
                        <div class="p-1 w-full text-center flex flex-row items-center justify-center text-center">
                            <input type='checkbox' 
                                @input="onInputChanged($event,row.id)"
                                class="cursor-pointer" 
                                v-model="rows[idx]['is_active']">
                            <button 
                                @click="onSave(row.id)"
                                v-show="row['changed']===true" 
                                class=" w-10 h-7 rounded text-xs p-1 cursor-pointer bg-green-100 hover:bg-green-200" 
                                title="save">
                                OK
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
         <button @click="onAddNew" title="tambah" class="bottom-5 right-5 fixed w-10 h-10 flex items-center justify-center font-bold rounded-full cursor-pointer bg-pink-400 hover:bg-pink-600">+</button>
    </div>
   
@endverbatim
<script>
    var app = new Vue({
        el: '#app',
        data: {
            rows:{},
            focusIdx:null
        },
        methods:{
            onAddNew(){
                const id = Date.parse(new Date())+this.rows.length+'-new'
                this.rows.push({
                    id:id,
                    is_active:true,
                    changed:false
                });
            },
            onInputChanged(evt,id){
                const foundIdxRow = this.rows.findIndex(dt=>dt.id===id)
                const row = this.rows[foundIdxRow]
                row['changed']=true
                this.rows[foundIdxRow] = row
            },
            onPDOInput(evt,id){
                const foundIdxRow = this.rows.findIndex(dt=>dt.id===id)
                const row = this.rows[foundIdxRow]
                row['prepared_query'] = evt.target.value
                row['changed']=true
                this.rows[foundIdxRow] = row
            },
            async onSave(id){
                const foundIdxRow = this.rows.findIndex(dt=>dt.id===id)
                const row = this.rows[foundIdxRow]
                const res = await fetch("{{url('laradev/paramaker')}}", {
                    method: "POST",
                    headers     : {
                        laradev:"{{env('LARADEVPASSWORD','bismillah')}}",
                        "Content-Type":"Application/json"
                    },
                    body: JSON.stringify(row),
                })
                if(!res.ok){
                    let response = await res.json()
                    alert('GAGAL! lihat console')
                    console.log(response)
                    return
                }
                let response = await res.text()
                if(!isNaN(response+1)){
                    row['id'] = response
                    alert('New Data Saved')
                }else{
                    alert('Updated')
                }
                row['changed'] = false
                this.rows[foundIdxRow] = row
            }
        },
        mounted(){
            var list = {!!$list!!};
            this.rows=list;
        }
    })
</script>
</body>
</html>