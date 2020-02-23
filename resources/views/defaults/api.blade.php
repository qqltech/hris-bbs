<html>
    <head>
        <title>Laradev Injector</title>
        <link rel="stylesheet" href="{{url('defaults/request2.css')}}">
        <link rel="stylesheet" href="{{url('defaults/codemirror.css')}}">
        <link rel="stylesheet" href="{{url('defaults/theme/monokai.css')}}">
        <link rel="stylesheet" href="{{url('defaults/addon/lint/lint.css')}}">
    </head>
    <body>
        <p><span style="padding:5 20px 5 20px;position:fixed;right:1%;top:10px;font-weight:bold;background-color:green;color:white" id="modelSelected"></span>
            
        </p>
        <div id="codemirror">
            <textarea id="code"></textarea>
            <p><button style="position:fixed;right:400px;bottom:35px; background-color:red;color:white" id="toggle">Hide!</button><a href="javascript:void(0)" class="button" id="run" style="margin-left:75% !important">Run on Console!</a></p>
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
                            columns : arrayData.columns,
                            parameters      :{
                                selectfield : "column1,column2,column3,dst",
                                where       : "column1='kata' AND column2=999",
                                orderby     : "id",
                                orderbype   : "ASC",
                                search      : "string",
                                searchfield : "column_tercari1,column_tercari2,dst",
                                paginate    : "100",
                                join        : true                                
                            }
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
                            if( arrayData.config.createable.includes(fullColumns[i].name) ){
                                var susunan = "";
                                susunan += fullColumns[i].nullable?"{required}-[":"{kosongan}-[";
                                susunan += (fullColumns[i].type).replace("\\","")+"]" ;
                                susunan += fullColumns[i].comment==""?"-<data:input>":"-<data:"+fullColumns[i].comment+">" ;
                                newForm[fullColumns[i].name] = susunan;
                            }
                        }
                        codemirror.setValue(JSON.stringify(newForm,null,"\t\t"));
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
                            payloadFields : arrayData.config.updateable
                        };
                        let fullColumns = arrayData.fullColumns;
                        var newForm = {};
                        for(let i=0; i<fullColumns.length;i++){
                            if( arrayData.config.updateable.includes(fullColumns[i].name) ){
                                var susunan = "";
                                susunan += fullColumns[i].nullable?"{required}-[":"{kosongan}-[";
                                susunan += (fullColumns[i].type).replace("\\","")+"]" ;
                                susunan += fullColumns[i].comment==""?"-<data:input>":("-<data:"+JSON.parse(fullColumns[i].comment).src)+">" ;
                                newForm[fullColumns[i].name] = susunan;
                            }
                        }
                        codemirror.setValue(JSON.stringify(newForm,null,"\t\t"));
                        document.getElementById("modelSelected").innerText=arrayData.model+"[UPDATE]";
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