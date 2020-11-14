<html>
    <head>
        <title>FRONTEND - Larahan</title>
        <link rel="icon" href="{{url('favicon.ico')}}">
        <link rel="stylesheet" href="{{url('defaults/request2.css')}}">
        <link rel="stylesheet" href="{{url('defaults/codemirror.css')}}">
        <link rel="stylesheet" href="{{url('defaults/theme/monokai.css')}}">
        <link rel="stylesheet" href="{{url('defaults/addon/lint/lint.css')}}">
    </head>
    <body>
        <p><span style="padding:5 5px 5 5px;position:fixed;right:1%;top:0px;font-weight:bold;background-color:green;color:white" id="modelSelected"></span>
            
        </p>
        <div id="codemirror">
            <textarea id="code"></textarea>
            <p><button style="position:fixed;right:10px;bottom:40px; background-color:red;color:white" id="toggle">Hide!</button>
            <!-- <a href="javascript:void(0)" class="button" id="run" style="margin-left:75% !important">Run on Console!</a> -->
            </p>
        </div>
        <div>
            <table border="1">
                <thead>
                    <th>Model</th>
                    <th colspan="4">Actions</th>
                </thead>
                <tbody>
                    <tr>
                        <td>Authorization</td>
                        <td colspan="2" align="center"><button href="javascript:void(0)">LOGIN</button></td>
                        <td colspan="2" align="center"><button href="javascript:void(0)">GET ME</button></td>
                    </tr>
                    @foreach($models as $key => $model)                        
                        <tr>
                            <td style="padding:0 5 0 5">{{$model->model}}</td>
                            <td><button class="read" href="javascript:void(0)" style="font-size:10px" index={{$key}}>READ</button></td>
                            <td><button class="create" href="javascript:void(0)" style="font-size:10px" index={{$key}}>CREATE</button></td>
                            <td><button class="update" href="javascript:void(0)" style="font-size:10px" index={{$key}}>UPDATE</button></td>
                            <td><button class="delete" href="javascript:void(0)" style="font-size:10px" index={{$key}}>DELETE</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <script src="{{url('defaults/axios.min.js')}}"></script>
            <script src="{{url('defaults/codemirror.js')}}"></script>
            <script src="{{url('defaults/addon/mode/loadmode.js')}}"></script>
            <script src="{{url('defaults/addon/mode/javascript.js')}}"></script>
            <script src="{{url('defaults/addon/search/searchcursor.js')}}"></script>
            <script src="{{url('defaults/addon/search/search.js')}}"></script>
            <script src="{{url('defaults/addon/dialog/dialog.js')}}"></script>
            <script src="{{url('defaults/addon/edit/matchbrackets.js')}}"></script>
            <script src="{{url('defaults/addon/edit/closebrackets.js')}}"></script>
            <script src="{{url('defaults/addon/comment/comment.js')}}"></script>
            <script src="{{url('defaults/addon/wrap/hardwrap.js')}}"></script>
            <script src="{{url('defaults/addon/fold/foldcode.js')}}"></script>
            <script src="{{url('defaults/addon/fold/brace-fold.js')}}"></script>
            <script src="{{url('defaults/addon/keymaps/sublime.js')}}"></script>
            <script src="{{url('defaults/addon/edit/matchbrackets.js')}}"></script>
            <script src="{{url('defaults/addon/comment/continuecomment.js')}}"></script>
            <script src="{{url('defaults/addon/comment/comment.js')}}"></script>
            <script src="{{url('defaults/addon/lint/jshint.js')}}"></script>
            <script src="{{url('defaults/addon/lint/lint.js')}}"></script>
            <script src="{{url('defaults/addon/lint/javascript-lint.js')}}"></script>
            <script src="{{url('defaults/addon/lint/css-lint.js')}}"></script>
            <script>
                var data = @php echo json_encode($models); @endphp;
                var codemirror = CodeMirror.fromTextArea(document.getElementById("code"), {
                    lineNumbers: false,
                    mode: "javascript",
                    viewportMargin: Infinity,
                    theme:"monokai",
                    keyMap:"sublime",
                    matchBrackets: true,
                    continueComments: "Enter",
                    lint: true
                  });
                
                var isToggled=false;
                document.getElementById("toggle").onclick = function(){
                    if(isToggled){
                        document.getElementById("codemirror").style.display = "block";
                        document.getElementById("modelSelected").style.display = "block";
                        isToggled = false;
                    }else{
                        document.getElementById("codemirror").style.display = "none";
                        document.getElementById("modelSelected").style.display = "none";
                        isToggled = true;
                    }
                };
                var classname = document.getElementsByClassName("read");
                Array.from(classname).forEach(function(element) {
                    element.addEventListener("click",function(){
                        codemirror.setValue("");
                        let index = element.getAttribute("index");
                        let arrayData = data[index];
                        var newData = {
                            url: "/operation/"+arrayData.model,
                            method: "GET",
                            headers: {
                                "authorization": "BearerToken",
                                "Cache-Control": "no-cache"
                            },
                            parameters      : {
                                selectfield : "column1,column2,column3,dst",
                                where       : "column1='kata' AND column2=999",
                                orderby     : "id",
                                ordertype   : "ASC",
                                orderbyraw  : "id ASC,col2 DESC",
                                search      : "stringpotongankata",
                                searchfield : "column_tercari1,column_tercari2,dst",
                                paginate    : "100",
                                join        : true,
                                joinmax     : 0,
                                addselect   : "column1,column2,column3,dst",
                                group_by    : "column1,column2,column3,dst",
                            },
                            basic_response  : arrayData.columns,
                            real_response  : "silahkan dicoba di operation"
                        };
                        codemirror.setValue(JSON.stringify(newData,null,"\t"));
                        document.getElementById("modelSelected").innerText=arrayData.model+"[GET]";
                        document.getElementById("codemirror").style.display = "block";
                        document.getElementById("modelSelected").style.display = "block";
                    });
                });
                var classname = document.getElementsByClassName("create");
                Array.from(classname).forEach(function(element) {
                    element.addEventListener("click",function(){
                        codemirror.setValue("");
                        let index = element.getAttribute("index");
                        let arrayData = data[index];
                        var newData = {
                            payloadFields : arrayData.config.createable,
                        };
                        let fullColumns = arrayData.fullColumns;
                        var newForm = {};
                        for(let i=0; i<fullColumns.length;i++){
                            if( arrayData.config.createable.includes(fullColumns[i].name) && !(fullColumns[i].comment).includes('fk') ){
                                var susunan = "";
                                susunan += fullColumns[i].nullable?"{required}-[":"{optional}-[";
                                susunan += (fullColumns[i].type).replace("\\","")+"]" ;
                                susunan += fullColumns[i].comment==""?"-<data:input>":"-<data:"+fullColumns[i].comment+">" ;
                                newForm[fullColumns[i].name] = susunan;
                            }
                        }
                        
                        (arrayData.details).forEach(dt=>{
                            let arrayDataDetail = data.find(dtl=>{
                                return dtl.model == dt;
                            });
                            let fullColumnsDetail = arrayDataDetail.fullColumns;
                            let detailsPayload = {};
                            for(let i=0; i<fullColumnsDetail.length;i++){
                                if( arrayDataDetail.config.createable.includes(fullColumnsDetail[i].name) && !(fullColumnsDetail[i].comment).includes('fk') ){
                                    var susunan = "";
                                    susunan += fullColumnsDetail[i].nullable?"{required}-[":"{optional}-[";
                                    susunan += (fullColumnsDetail[i].type).replace("\\","")+"]" ;
                                    susunan += fullColumnsDetail[i].comment==""?"-<data:input>":"-<data:"+fullColumnsDetail[i].comment+">" ;
                                    detailsPayload[fullColumnsDetail[i].name] = susunan;
                                }
                                (arrayDataDetail.details).forEach(subdt=>{
                                    let arrayDataDetailDetail = data.find(subdtl=>{
                                        return subdtl.model == subdt;
                                    });
                                    let fullColumnsDetailHeirs = arrayDataDetailDetail.fullColumns;
                                    let detailsPayloadHeirs = {};
                                    for(let i=0; i<fullColumnsDetailHeirs.length;i++){
                                        if( arrayDataDetailDetail.config.createable.includes(fullColumnsDetailHeirs[i].name) && !(fullColumnsDetailHeirs[i].comment).includes('fk') ){
                                            var susunan = "";
                                            susunan += fullColumnsDetailHeirs[i].nullable?"{required}-[":"{optional}-[";
                                            susunan += (fullColumnsDetailHeirs[i].type).replace("\\","")+"]" ;
                                            susunan += fullColumnsDetailHeirs[i].comment==""?"-<data:input>":"-<data:"+fullColumnsDetailHeirs[i].comment+">" ;
                                            detailsPayloadHeirs[fullColumnsDetailHeirs[i].name] = susunan;
                                        }
                                        detailsPayload[subdt] = [detailsPayloadHeirs];
                                    }
                                });
                                newForm[dt] = [detailsPayload];
                            }
                        });
                        codemirror.setValue(JSON.stringify(newForm,null,"\t"));
                        document.getElementById("modelSelected").innerText=arrayData.model+"[CREATE]";
                        document.getElementById("codemirror").style.display = "block";
                        document.getElementById("modelSelected").style.display = "block";
                    });
                });
                var classname = document.getElementsByClassName("update");
                Array.from(classname).forEach(function(element) {
                    element.addEventListener("click",function(){
                        codemirror.setValue("");
                        let index = element.getAttribute("index");
                        let arrayData = data[index];
                        var newData = {
                            payloadFields : arrayData.config.createable,
                        };
                        let fullColumns = arrayData.fullColumns;
                        var newForm = {};
                        for(let i=0; i<fullColumns.length;i++){
                            if( arrayData.config.createable.includes(fullColumns[i].name) ){
                                var susunan = "";
                                susunan += fullColumns[i].nullable?"{required}-[":"{optional}-[";
                                susunan += (fullColumns[i].type).replace("\\","")+"]" ;
                                susunan += fullColumns[i].comment==""?"-<data:input>":"-<data:"+fullColumns[i].comment+">" ;
                                newForm[fullColumns[i].name] = susunan;
                            }
                        }
                        
                        (arrayData.details).forEach(dt=>{
                            let arrayDataDetail = data.find(dtl=>{
                                return dtl.model == dt;
                            });
                            let fullColumnsDetail = arrayDataDetail.fullColumns;
                            let detailsPayload = {};
                            for(let i=0; i<fullColumnsDetail.length;i++){
                                if( arrayDataDetail.config.createable.includes(fullColumnsDetail[i].name)  ){
                                    var susunan = "";
                                    susunan += fullColumnsDetail[i].nullable?"{required}-[":"{optional}-[";
                                    susunan += (fullColumnsDetail[i].type).replace("\\","")+"]" ;
                                    susunan += fullColumnsDetail[i].comment==""?"-<data:input>":"-<data:"+fullColumnsDetail[i].comment+">" ;
                                    detailsPayload[fullColumnsDetail[i].name] = susunan;
                                }
                                (arrayDataDetail.details).forEach(subdt=>{
                                    let arrayDataDetailDetail = data.find(subdtl=>{
                                        return subdtl.model == subdt;
                                    });
                                    let fullColumnsDetailHeirs = arrayDataDetailDetail.fullColumns;
                                    let detailsPayloadHeirs = {};
                                    for(let i=0; i<fullColumnsDetailHeirs.length;i++){
                                        if( arrayDataDetailDetail.config.createable.includes(fullColumnsDetailHeirs[i].name) ){
                                            var susunan = "";
                                            susunan += fullColumnsDetailHeirs[i].nullable?"{required}-[":"{optional}-[";
                                            susunan += (fullColumnsDetailHeirs[i].type).replace("\\","")+"]" ;
                                            susunan += fullColumnsDetailHeirs[i].comment==""?"-<data:input>":"-<data:"+fullColumnsDetailHeirs[i].comment+">" ;
                                            detailsPayloadHeirs[fullColumnsDetailHeirs[i].name] = susunan;
                                        }
                                        detailsPayload[subdt] = [detailsPayloadHeirs];
                                    }
                                });
                                newForm[dt] = [detailsPayload];
                            }
                        });
                        codemirror.setValue(JSON.stringify(newForm,null,"\t"));
                        document.getElementById("modelSelected").innerText=arrayData.model+"[CREATE]";
                        document.getElementById("codemirror").style.display = "block";
                        document.getElementById("modelSelected").style.display = "block";
                    });
                });
                var classname = document.getElementsByClassName("delete");
                Array.from(classname).forEach(function(element) {
                    element.addEventListener("click",function(){
                        codemirror.setValue("");
                        let index = element.getAttribute("index");
                        let arrayData = data[index];
                        document.getElementById("modelSelected").innerText=arrayData.model+"[DELETE]";
                        document.getElementById("codemirror").style.display = "block";
                        document.getElementById("modelSelected").style.display = "block";
                    });
                });
            </script>
    </body>
</html>