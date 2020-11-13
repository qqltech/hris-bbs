<html>
<head>
    <title> UPLOADER </title>
    <link rel="icon" href="{{url('favicon.ico')}}">
    <script src="{{url('defaults/vue.min.js')}}"></script>
    <script src="https://unpkg.com/vue-select@3.0.0"></script>
    <link rel="stylesheet" href="https://unpkg.com/vue-select@3.0.0/dist/vue-select.css">
    <link rel="stylesheet" href="{{url('defaults/tailwind.min.css')}}">
    <script src="{{url('defaults/axios.min.js')}}"></script>
@verbatim
</head>
<body>
<div class="container">
    <div id="app">
        <v-select 
            placeholder="Pilih table yang akan diupload"
            :options="tablesComplete" 
            label="model"
            v-model="selectedTable"
            @input="tableSelected"
            style="margin-left: auto;margin-right: auto;width:40%;margin-bottom:5px;">
        </v-select>
        
        <div style="padding-left:23%;">
            <textarea :disabled="selectedTable===null" class='bg-blue-100' style="width:70%;font-size:10px;resize: none;height:25%" v-model="excelValue" placeholder="paste excel here" @input="processExcel"></textarea>
        </div>
        <div style="padding-left:23%;">
            <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full"
                style="margin-left: auto;margin-right: auto;margin-top:5px;" @click="apiLengkapi">
                Lengkapi!
            </button>
            <button class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-full"
                style="margin-left: auto;margin-right: auto;margin-top:5px;">
                Test Upload!
            </button>
            <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-full"
                style="margin-left: auto;margin-right: auto;margin-top:5px;">
                Final Upload!
            </button>
            <button class="bg-pink-500 hover:bg-pink-700 text-white font-bold py-2 px-4 rounded-full"
                style="margin-left: auto;margin-right: auto;margin-top:5px;" @click="copyToClipboard">
                Copy to Clipboard!
            </button>
        </div>
        <table class="table-auto" style="border: 0.2px solid black;margin-top:10px;" >
            <thead>
            <th v-for="(item, index) in headersQuery" style="border: 1px solid black;" class="bg-purple-300">
                    <input type='text' placeholder='queryAll' v-model="headersQuery[index]" @input="query(index)">
            </th>
            </thead>
            <thead>
            <th v-for="(item, index) in headers" style="border: 1px solid black;" :class="headersRequired.includes(item)?'bg-pink-200':(headersOriginal.includes(item)?'bg-green-200':'')">
                {{item}}
            </th>
            </thead>
            <tbody>
                <tr v-for="(item, index) in bodyArray">
                    <td v-for="(itemChild, indexChild) in item" style="border: 1px solid black;">
                        <input type='text' dalue="itemChild" v-model="bodyArray[index][indexChild]" @input="bodyJson[index][headers[indexChild]]=bodyArray[index][indexChild]">
                    </td>
                </tr>
            </tbody>
        </table>
        
    </div>
</div>
@endverbatim

<script>
Vue.component('v-select', VueSelect.VueSelect);
var app = new Vue({
    el: '#app',
    watch: {},
    data: {
        port: '8080',
        excelValue:"",
        headers : [],
        headersOriginal : [],
        headersRequired:[],
        headersUnion :[],
        headersQuery : [],
        bodyJson:[],
        bodyArray:[],
        tablesComplete:[],
        selectedTable:null
    },
    computed:{
        // bodyArr:function(){
        //     let data = me.bodyJson;
        //     let newArray = 
        // }
    },
    created(){           
        var me = this; 
        var xmlhttp = new XMLHttpRequest();
        var url = "{{url('models.json')}}";
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                var myArr = JSON.parse(xmlhttp.responseText);
                me.tablesComplete=myArr;
            }
        };
        xmlhttp.open("GET", url, true);
        xmlhttp.send();
    },
    methods: {
        copyToClipboard(){
            let str = "";
            str+=this.headers.join("\t");
            str+="\n";
            this.bodyArray.forEach(dt=>{
                str+=dt.join("\t");
                str+="\n";
            })
            const el = document.createElement('textarea');
            el.value = str;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
        },
        apiLengkapi(){
            let me = this;
            me.submitApi({
                url  : "{{url('laradev/uploadlengkapi')}}",
                data : {
                    data:me.bodyArray,
                    table:me.selectedTable.model 
                }
            },function(response){
                // let bodyArrayNew = [];
                // response=response.data;
                // response.forEach(dt=>{
                //     bodyArrayNew.push(Object.values(dt));
                // })
                console.log(response.data)
                me.bodyArray = response.data;
                // me.bodyJson = response.data.data;
            })
        },
        query(i){
            let val = this.headersQuery[i];
            let valOriginal = val;
            let value =val;
            for(let index in this.bodyArray){
                if(valOriginal.includes("<") && valOriginal.includes(">")){
                    value = valOriginal;
                    for(let indexJson in this.bodyJson[index]){
                        let reg = `<${indexJson}>`;
                        value = value.replace(new RegExp(reg,'g'),this.bodyJson[index][indexJson]);
                    }
                }
                this.bodyArray[index][i] = value;
                this.bodyJson[index][this.headers[i]] = value;
            }
        },
        tableSelected(table){
            this.headers = table.columns.filter(dt=>{
                return !["created_at","updated_at"].includes(dt);
            });
            this.headersOriginal=this.headers;
            this.headersRequired = table.config.required;
            this.bodyJson=[];
            this.bodyArray=[];
            this.headersQuery=[];
            this.headers.forEach(dt=>{
                this.headersQuery.push("");
            })
        },
        processExcel(){
            let me = this;
            this.bodyArray=[];
            let val = this.excelValue;
            let arrayBaris = val.split("\n");
            let headers = arrayBaris[0].split("\t");
            headers.forEach( (dt,i)=>{
                try{
                    headers[i] = headers[i].toLowerCase();
                }catch(e){}
            })
            this.headers = headers;
            let body = [];
            arrayBaris.shift();
            arrayBaris.forEach(dt=>{
                let dataJson = {};
                let bodyArray = dt.split("\t");
                headers.forEach( (head,index)=>{
                    dataJson[head] = bodyArray[index];
                });
                if(bodyArray.length==headers.length){
                    body.push(dataJson);
                    me.bodyArray.push(bodyArray);
                }
            });
            let indexTambahan = 0;
            let keyTambahan = [];
            for( let i in this.headersOriginal){
                if(!this.headers.includes(this.headersOriginal[i])){
                    this.headers.unshift(this.headersOriginal[i]);
                    indexTambahan++;
                    keyTambahan.push(this.headersOriginal[i]);
                }
            }
            if(indexTambahan>0){
                me.bodyArray = me.bodyArray.map(dt=>{
                    let tambahanArray = [];
                    for(let i=1;i<=indexTambahan;i++){
                        dt.unshift("");
                    }
                    return dt;
                })
                body = body.map(dt=>{
                    for(let i=0;i<indexTambahan;i++){
                        dt[keyTambahan[i]]=null;
                    }
                    return dt;
                })
            }
            this.headersQuery=[];
            this.headers.forEach(dt=>{
                this.headersQuery.push("");
            })
            this.bodyJson = body;
        },
        // var url = "{{url('laradev/migrations')}}";
        submitApi(data,callback=function(response){}){
            var $options   =
            {
                url         : data.url,
                credentials : true,
                method      : 'POST',
                data        : data.data,
                headers     : {
                    laradev:"quantumleap150671"
                }
            }
            axios($options).then(response => {
                console.log(response)
                callback(response);
            }).catch(error => {
                
            }).then(function () {
                //GAGAL BERHASIL SELALU DILAKSANAKAN
            });  ;
        }
    },
})
</script>
</body>
</html>