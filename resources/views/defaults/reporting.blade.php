<html>
    <head>
        <title> REPORTING </title>
        <link rel="icon" href="{{url('favicon.ico')}}">
        <script src="{{url('defaults/vue.min.js')}}"></script>
        <script src="{{url('defaults/axios.min.js')}}"></script>
        <script src="https://unpkg.com/vue-select@3.0.0"></script>
        <link rel="stylesheet" href="https://unpkg.com/vue-select@3.0.0/dist/vue-select.css">
        @verbatim
    </head>
<body>
    <div id="app">
        <form ref="form" :action="actionFixed" method="post" target="_blank">
            <input name='type' type="hidden" value="pdf" ref='type'>
            <input name='sheetname' type="hidden" value="header">
            <input name='title' type="hidden" value="test">
            <textarea ref='config' name="config" style="width:100%;height:33%" v-model="dataexcel" @input="paste"></textarea>
        </form>
        <div style="display:block;clear:both;margin-left: auto;margin-right: auto;width:80%;margin-bottom:5px;">
            <div style="margin-left: auto;margin-right: auto;width:50%;margin-bottom:5px;">
                <v-select 
                    placeholder="Pilih Template"
                    :options="selectTemplateOptions" 
                    label="name"
                    v-model="selectedTemplate"
                    @input="selectChange"
                    style="float:left;width:45%;margin-right:7px;margin-bottom:5px;"
                >
                </v-select>
                <input type='text' placeholder='template name' v-model="templateNameNew" style="width:40%;min-height:35px;margin-right:5px;" v-if="selectedTemplate!==null && selectedTemplate['name']=='Create New'">
                <a href="#" style="margin-top:5px;" @click="save" v-if="selectedTemplate!==null" >Save</a>
            </div>
        </div>
        <div>
            <table style="width:100%;border-collapse: collapse;" cellspacing="0">
                <tr v-for="(tr,i) in arrayexcelRead" :key="i">
                    <td v-for="(td,j) in tr" :key="j" :colspan="getColspan(i,j)" :rowspan="getRowspan(i,j)"  v-if="td!=''" :style="getStyle(td)">
                        {{getData(i,td)}}
                    </td>
                </tr>
            </table>
        </div>
    </div>
    @endverbatim
<script>
Vue.component('v-select', VueSelect.VueSelect);
var list = {!!$list!!};
list.push({
    name:'Create New',id:null,template:null
})
var app = new Vue({
    el: '#app',
    watch: {},
    data: {
        isLoading:false,
        selectTemplateOptions:list,
        selectedTemplate:null,
        templateNameNew:null,
        type:"html",
        dataexcel:"",
        arrayexcel:[],
        startBody:1,
        action:"http://localhost/larahan/public/ace/coba",
        actionFixed:"",
        maxCol:0
    },
    computed:{
        arrayexcelRead:function(){
            let newData = (this.arrayexcel).filter(dt=>{
                return dt.length>1
                return dt.length==this.maxCol
            });
            return newData??[];
        }
    },
    created(){ 
                this.dataexcel = `PT Makmur Indonesia::trb						
$no_rekening  $nama_rekening::tcb						
Periode $from sampai $to::tcb						
						
NO::hcg	REKENING::hgc	NAMA REKENING::hgc	SALDO AWAL::hgc	DEBET::hgc	KREDIT::hgc	SALDO AKHIR::hgc
_number::cb	$data.no_rekening	$data.nama_rekening	$data.saldo_awal::.r	$data.debet::.r	$data.credit::.r	$data.saldo_akhir::.r
_number::cb	$data.no_rekening	$data.nama_rekening	$data.saldo_awal::.r	$data.debet::.r	$data.credit::.r	$data.saldo_akhir::.r
?	?	?	$sum.data.saldo_awal::.ry	$sum.data.debet::.ry	$sum.data.credit::.ry	$sum.data.debet-$sum.data.credit::.ry
`;      
        this.paste();
    },
    methods:{
        save(){
            let me = this;
            var $options   =
            {
                url         : "{{url('laradev/uploadtemplate')}}",
                credentials : true,
                method      : 'POST',
                data        : {
                    table:"{{$table}}",
                    template:me.dataexcel,
                    name:me.templateNameNew!==null?me.templateNameNew:me.selectedTemplate.name,
                    id:me.selectedTemplate.id
                },
                headers     : {
                    laradev:"{{env('LARADEVPASSWORD','bismillah')}}",
                }
            }
            me.isLoading=true;
            axios($options).then(response => {
                response.data.push({
                    name:'Create New',id:null,template:null
                })
                me.selectTemplateOptions=response.data;
                alert('saved successfully');
            }).catch(errors => {
                console.log(errors)
            }).then(function () {
                me.isLoading=false;
            });
        },
        selectChange(val){
            this.dataexcel = "";
            if(val===null){
                this.dataexcel = null;this.paste();return;
            }
            if(val.name!='Create New'){
                this.templateNameNew=null;
            }
            this.dataexcel = val.template;
            this.paste();
        },
        submitForm(url,type='html'){
            this.type=type;
            // this.$refs.config.value=;
            this.$refs.type.value=type;
            this.$refs.form.action='http://localhost/larahan/public/ace/coba';
            this.$refs.form.submit()
        },
        paste(){
            let data;
            try{
                data = this.dataexcel.split("\n");
            }catch(e){
                return;
            }
            this.arrayexcel=[];
            for(let i in data){
                let tds = data[i].split("\t");
                if( tds.length>this.maxCol ){
                    this.maxCol=tds.length;
                }
                this.arrayexcel.push(tds);
                if(tds[0].includes("::")){
                    let format = tds[0].split("::");
                    if( (format[format.length-1]).includes("h") ){
                        this.startBody=i;
                    }
                }
            }
        },
        getColspan(i,j){
            let data = this.arrayexcel[i][j];
            let baris = this.arrayexcel[i];
            let arrayData = this.arrayexcel;
            let hitung = 1;
            for(let index=j+1;index<baris.length;index++){
                
                if(baris[index]==""&& ((arrayData[i-1])!==undefined && this.arrayexcel[i-1][index]=='') || arrayData[i-1]===undefined   ){
                    hitung++;
                }else{
                    break;
                };
            }
            return hitung;
        },
        getRowspan(i,j){
            let data = this.arrayexcel;
            let baris = this.arrayexcel;
            let hitung = 1;
            for(let index=i+1;index<baris.length;index++){
                if(baris[index][j]==""){
                    hitung++;
                }else{
                    break;
                };
            }
            return hitung;
        },
        getStyle(dt){
            if(dt=="?"){
                return "border:0px;";
            }
            let borderStyling=false;
            let style ="padding-left:3px;padding-right:3px;";
            if(!dt.includes("::")){
                return style+="border-bottom:1px solid black;border-top:1px solid black;border-left:1px solid black;border-right:1px solid black;";
            }
            dtArray = dt.split("::");
            dt = dtArray[dtArray.length-1];
            if(dt.includes("b")|| dt.includes("h")){
                style+="font-weight:bold;";
            }
            if(dt.includes("c")){
                style+="text-align:center;";
            }
            if(dt.includes("r")){
                style+="text-align:right;";
            }                    
            if(dt.includes("l")){
                style+="text-align:left;";
            }
            if(dt.includes("_")){
                style+="border-bottom:1px solid black;";borderStyling=true;
            }              
            if(dt.includes("-")){
                style+="border-top:1px solid black;";borderStyling=true;
            }              
            if(dt.includes("[")){
                style+="border-left:1px solid black;";borderStyling=true;
            }       
            if(dt.includes("[")){
                style+="border-right:1px solid black;";borderStyling=true;
            }
            if(dt.includes("+")||borderStyling===false){
                style+="border-bottom:1px solid black;border-top:1px solid black;border-left:1px solid black;border-right:1px solid black;";
            }
                    
            if(dt.includes("g")){
                style+="background-color:#dad4d4;";
            }       
            if(dt.includes("y")){
                style+="background-color:#f2ee74;";
            }  
            if(dt.includes("=")){
                style=style.replace(/1px solid /g," double ");
            }
            if(dt.includes("w")){
                let temp = dt.split("w");
                temp = temp[1].split("%")[0];
                style+="width:"+temp+"%;";
            }
            

            return style;
        },
        getData(index,val){
            newVal = val!="?"?(val.replace(/\t/g,"")).split('::')[0]:'';
            newVal = newVal.replace(/\n/g,"")
            if(val.includes("::")){
                let format = val.split("::")[1];
                if(format.includes(".")){
                    // newVal = parseFloat(newVal).toLocaleString('id');
                }
            }
            if(newVal=='_number'){
                newVal = 'auto';//index-this.startBody;
                // this.startBody+=1;
            }else{
                newVal = newVal.replace(/\t/g,"");
                newVal = newVal.replace(/'/g,"");
            }
            return newVal;
        }
    }
});
</script>  
</body>

</html>