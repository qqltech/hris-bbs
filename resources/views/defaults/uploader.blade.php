<html>
<head>
    <title> UPLOADER </title>
    <link rel="icon" href="{{url('favicon.ico')}}">
    <script src="{{url('defaults/vue.min.js')}}"></script>
@verbatim
</head>
<body>
<div class="container">
    <div id="app">
        <div style="padding-left:23%;">
            <textarea style="width:70%;font-size:10px;resize: none;height:25%" v-model="excelValue" placeholder="paste excel here" @input="processExcel"></textarea>
        </div>
        <table class="table-auto" style="border: 0.2px solid black;margin-top:10px;" >
            <thead>
            <th v-for="(item, index) in headers" style="border: 1px solid black;">
                {{item}}
            </th>
            </thead>
            <tbody>
                <tr v-for="(item, index) in bodyArray">
                    <td v-for="(itemChild, indexChild) in item">
                        <input type='text' :value="itemChild">
                    </td>
                </tr>
            </tbody>
        </table>
        
    </div>
</div>
@endverbatim

<script>
var app = new Vue({
    el: '#app',
    watch: {},
    data: {
        port: '8080',
        excelValue:"",
        headers : [],
        bodyJson:[],
        bodyArray:[]
    },
    created(){
    },
    methods: {
        test(){return 'abc';},        
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
            this.bodyJson = body;
        }
    },
})
</script>
</body>
</html>