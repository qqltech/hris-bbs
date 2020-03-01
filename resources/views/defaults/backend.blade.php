<html>
    <head>
        <title>Laradev Injector</title>
        <link rel="stylesheet" href="{{url('defaults/request2.css')}}">
        <link rel="stylesheet" href="{{url('defaults/codemirror.css')}}">
        <link rel="stylesheet" href="{{url('defaults/theme/monokai.css')}}">
        <link rel="stylesheet" href="{{url('defaults/addon/lint/lint.css')}}">
        <link rel="stylesheet" href="{{url('defaults/addon/hint/show-hint.css')}}">
        <style>
            #codemirror{
                width:60% !important;
                bottom:0px !important;
            }
            
            .CodeMirror{
                z-index: 50;
                height:98% !important;
                width:auto !important;
            }
            .cm-s-monokai span.cm-keyword {
                font-weight: bold;
                color: #f92672;
            }
            .cm-s-monokai span.cm-string {
                color: #f5e658;
            }
            .cm-s-monokai span.cm-variable-2 {
                color: #fff;
            }.cm-s-monokai span.cm-variable {
                color: #8fec0f;
            }.cm-s-monokai.CodeMirror {
                /* background: #161614 !important; */
            }.cm-s-monokai span.cm-comment {
                color: #464543;
            }
            .cm-s-monokai span.cm-def {
                color: #66ffef;
            }
            .CodeMirror-lines { padding-left: 10px; padding-top:10px; padding-bottom:10px }
        </style>
    </head>
    <body>
        <p><span style="padding:5 20px 5 20px;position:fixed;right:40px;top:10px;font-weight:bold;background-color:green;color:white" id="modelSelected"></span>
            <button style="position:fixed;right:0px;top:10px; background-color:red;color:white" id="toggle">Hide!</button>
        </p>
        <button style="position:fixed;right:0px;bottom:10px; background-color:green;color:white;z-index:51" id="toggle_full">full!</button>
        <div id="codemirror">
            <textarea id="code"></textarea>
        </div>
        <div>
            <table border="1">
                <thead>
                    <th>Migrations<button id="new" style="background-color:greenyellow">+</button></th>
                    <th colspan="6">Actions <button id="real_fk" style="background-color:greenyellow">Set FK</button><button id="drop_fk" style="background-color:pink">Drop ({{$realfk}}) FK</button></th>
                </thead>
                <tbody>
                    @foreach($models as $key => $model)
                        @if( !(strpos($model['file'], 'oauth') !== false))
                            <tr>
                                <td style="padding:0 5 0 5" id="data-{{$key}}">{{ str_replace(".php","",$model['file'])}}</td>
                                <td><button class="migration" href="javascript:void(0)" style="font-size:10px" index={{$key}}>Migration</button></td>
                                <td><button class="model" href="javascript:void(0)" style="font-size:10px;"  index={{$key}}
                                    @if( strpos($model['file'],"_after_" ) !==false || strpos($model['file'],"_before_" ) !==false ) disabled @endif
                                    >Model</button></td>
                                <td><button class="migrate" href="javascript:void(0)" style="font-size:10px" index={{$key}} 
                                    @if( $model['alias'] ) disabled @endif
                                    >@if($model['alias']) Alias&nbsp;&nbsp; @else Migrate @endif</button></td>
                                <td><button class="down" href="javascript:void(0)" style="font-size:10px" index={{$key}} 
                                        @if( $model['alias'] ) disabled @endif
                                        >@if($model['alias']) Alias&nbsp;&nbsp; @else Down @endif</button></td>
                                <td><button class="rename" href="javascript:void(0)" style="font-size:10px" 
                                    @if( strpos($model['file'],"_after_" ) !==false || strpos($model['file'],"_before_" ) !==false || (strpos($model['file'], 'default_') !== false || $model['alias']) ) disabled @endif
                                    index={{$key}}>Rename</button></td>
                                <td><button class="delete" href="javascript:void(0)" style="font-size:10px" 
                                    @if( (strpos($model['file'], 'default_') !== false) ) disabled @endif
                                    index={{$key}}>Delete</button></td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        <script src="{{url('defaults/axios.min.js')}}"></script>
            <script src="{{url('defaults/codemirror.js')}}"></script>
            <script src="{{url('defaults/addon/mode/loadmode.js')}}"></script>
            <script src="{{url('defaults/addon/mode/php.js')}}"></script>
            <script src="{{url('defaults/mode/clike/clike.js')}}"></script>
            <script src="{{url('defaults/mode/htmlmixed/htmlmixed.js')}}"></script>
            <script src="{{url('defaults/mode/xml/xml.js')}}"></script>
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
            <script src="{{url('defaults/addon/hint/html-hint.js')}}"></script>
            {{-- <script src="{{url('defaults/addon/lint/html-lint.js')}}"></script> --}}
            <script src="{{url('defaults/addon/lint/css-lint.js')}}"></script>
            <script>
                var lastid = null;
                var currentmigration = null;
                var currentmodel= null;
                var submitApi = (data,callback=function(response){})=>{
                    var $options   =
                    {
                        url         : data.url,
                        credentials : true,
                        method      : data.method,
                        data        : data.body,
                        headers     : {
                            laradev:"quantumleap150671"
                        }
                    }
                    if(data.method.toLowerCase() == "get"){
                        $options["params"] = data.body;
                    }
                    axios($options).then(response => {
                        window.console.clear();
                        console.log(response);
                        callback(response);
                    }).catch(error => {
                        window.console.clear();
                        alert("gagal, lihat console!");
                        console.log(error.response);
                    }).then(function () {
                        //GAGAL BERHASIL SELALU DILAKSANAKAN
                    });  ;
                }

                document.getElementById("new").addEventListener("click",function(e){
                    var modul = prompt("Nama Migration (standard : (3)modul_(3)submodul_processname):", "");
                    if (modul == null || modul == "") {
                    } else {
                        var url = "{{url('laradev/migrations')}}";
                        submitApi({
                            url : url,
                            method: "post",
                            body:{
                                modul:modul
                            }
                        },function(response){
                            window.location.reload();
                            console.log(response);
                        });
                    }
                });
                document.getElementById("real_fk").addEventListener("click",function(e){                   
                    if(confirm('Pasang semua Pyhsical Foreign Keys??')){
                        var url = "{{url('laradev/dorealfk')}}";
                        submitApi({
                            url : url,
                            method: "get",
                            body:null
                        },function(response){
                            window.location.reload();
                            console.log(response);
                        });
                    }
                });
                document.getElementById("drop_fk").addEventListener("click",function(e){                    
                    if(confirm('Hapus semua Physical Foreign Keys?')){
                        var url = "{{url('laradev/dorealfk')}}?drop=true";
                        submitApi({
                            url : url,
                            method: "get",
                            body:null
                        },function(response){
                            window.location.reload();
                            console.log(response);
                        });
                    }
                });

                var data = @php echo json_encode($models); @endphp;
                var codemirror = CodeMirror.fromTextArea(document.getElementById("code"), {
                    lineNumbers: false,
                    mode: "php",
                    viewportMargin: Infinity,
                    theme:"monokai",
                    keyMap:"sublime",
                    matchBrackets: true,
                    continueComments: "Enter",
                    lint: true,
                  });
                    var map = {"Ctrl-S": function(cm){
                        var valueText = cm.getValue();
                        var currentData = currentmodel==null?currentmigration:currentmodel;
                        var url = `{{url('laradev')}}/${currentmodel==null?'migrations':'models'}/`+(currentData).replace(".php","");
                        submitApi({
                            url : url,
                            method: "put",
                            body:{
                                text : valueText
                            }
                        },function(response){
                            console.log(response);
                            alert(`${currentmodel==null?'migration':'model'} berhasil tersimpan`);
                        });

                    }}
                    codemirror.addKeyMap(map);
                    // if(localStorage.valueText!=undefined){
                    //     codemirror.setValue(localStorage.valueText);
                    // }

                var isToggled=true;
                document.getElementById("codemirror").style.display = "none";
                document.getElementById("modelSelected").style.display = "none";
                // var isFull = false;
                // document.getElementById("toggle_full").onclick = function(){
                //     if(isFull){
                //         codemirror.setSize(300,200);
                //         isFull = false;
                //     }else{
                //         codemirror.setSize(500,1200);
                //         isFull = true;
                //     }
                // };

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
                window.addEventListener('keydown',function(e){
                    if(e.altKey && e.key=="`"){
                        if(isToggled){
                            document.getElementById("codemirror").style.display = "block";
                            document.getElementById("modelSelected").style.display = "block";
                            isToggled = false;
                        }else{
                            document.getElementById("codemirror").style.display = "none";
                            document.getElementById("modelSelected").style.display = "none";
                            isToggled = true;
                        }
                    }
                });
                var classname = document.getElementsByClassName("migration");
                Array.from(classname).forEach(function(element) {
                    element.addEventListener("click",function(){
                        
                        // Array.from(classname).forEach(function(el){
                        //     el.style.backgroundColor="white";
                        // });
                        // element.style.backgroundColor="green";
                        
                        let index = element.getAttribute("index");
                        let arrayData = data[index];
                        var url = "{{url('laradev/migrations')}}/"+(arrayData.file).replace(".php","");
                        submitApi({
                            url : url,
                            method: "get",
                            body:null
                        },function(response){
                            codemirror.setValue(response.data);
                            currentmodel = null;
                            currentmigration = (arrayData.file).replace(".php","");
                            if(lastid!==null){
                                document.getElementById(`data-${lastid}`).style.backgroundColor = "transparent";
                                document.getElementById(`data-${lastid}`).style.color = "black";
                            }
                            document.getElementById(`data-${index}`).style.backgroundColor = "green";
                            document.getElementById(`data-${index}`).style.color = "white";
                            lastid=index;

                        });
                        document.getElementById("modelSelected").innerText=arrayData.file+" [MIGRATION]";
                        document.getElementById("codemirror").style.display = "block";
                        document.getElementById("modelSelected").style.display = "block";
                    });
                });
                var classname = document.getElementsByClassName("model");
                Array.from(classname).forEach(function(element) {
                    
                    let index = element.getAttribute("index");
                    let arrayData = data[index];
                    if(!arrayData.model && !arrayData.alias ){
                        element.style.backgroundColor="red";
                    }
                    if( (arrayData.file).includes("_after_") || (arrayData.file).includes("_before_") ){
                        element.style.display="none";
                    }
                    element.addEventListener("click",function(e){
                    
                        let index = element.getAttribute("index");
                        let arrayData = data[index];
                        if(!arrayData.model){
                            alert("model belum ada, silahkan migrate dahulu");
                            e.preventDefault();
                            return;
                        }
                        var url = "{{url('laradev/models')}}/"+(arrayData.file).replace(".php","");
                        submitApi({
                            url : url,
                            method: "get",
                            body:null
                        },function(response){
                            codemirror.setValue(response.data.text);
                            currentmigration = null;
                            currentmodel = (arrayData.file).replace(".php","");
                            if(lastid!==null){
                                document.getElementById(`data-${lastid}`).style.backgroundColor = "transparent";
                                document.getElementById(`data-${lastid}`).style.color = "black";
                            }
                            document.getElementById(`data-${index}`).style.backgroundColor = "blue";
                            document.getElementById(`data-${index}`).style.color = "white";
                            lastid=index;
                        });
                        document.getElementById("modelSelected").innerText=arrayData.file+" [MODEL]";
                        document.getElementById("codemirror").style.display = "block";
                        document.getElementById("modelSelected").style.display = "block";
                    });
                });
                var classname = document.getElementsByClassName("migrate");
                Array.from(classname).forEach(function(element) {
                    
                    let index = element.getAttribute("index");
                    let arrayData = data[index];
                    if(!arrayData.table && !arrayData.alias){
                        element.style.backgroundColor="red";
                    }
                    element.addEventListener("click",function(){
                       
                        let index = element.getAttribute("index");
                        let arrayData = data[index];
                        if(confirm('Table dan BasicModel akan ter-replace?')){
                            var url = "{{url('laradev/migrate')}}/"+(arrayData.file).replace(".php","");
                            submitApi({
                                url : url,
                                method: "get",
                                body:null
                            },function(response){
                                // codemirror.setValue(response.data.text);
                                window.location.reload();
                                // console.log(response);
                            });
                        }
                    });
                });
                var classname = document.getElementsByClassName("delete");
                Array.from(classname).forEach(function(element) {
                    element.addEventListener("click",function(){
                        let index = element.getAttribute("index");
                        let arrayData = data[index];
                        var password = prompt(`[${(arrayData.file).replace(".php","")}] Migration, Model, Table akan hilang!, password:`, "");
                        if (password == null || password == "") {
                        } else {
                            var url = "{{url('laradev/trio')}}/"+(arrayData.file).replace(".php","");
                            submitApi({
                                url : url,
                                method: "delete",
                                body:{
                                    password : password
                                }
                            },function(response){
                                window.location.reload();
                            });
                        }
                    });
                });
                var classname = document.getElementsByClassName("rename");
                Array.from(classname).forEach(function(element) {
                    element.addEventListener("click",function(){
                        var table = prompt("New Migration Name:", "");
                        if (table == null || table == "") {
                        } else {
                            let index = element.getAttribute("index");
                            let arrayData = data[index];
                            var url = "{{url('laradev/tables')}}/"+(arrayData.file).replace(".php","");
                            submitApi({
                                url     : url,
                                method  : "PUT",
                                body    : {
                                    "name": table,
                                    "models": true
                                }
                            },function(response){
                                window.location.reload();
                            });
                        }
                    });
                });
                var classname = document.getElementsByClassName("down");
                Array.from(classname).forEach(function(element) {
                    
                    let index = element.getAttribute("index");
                    let arrayData = data[index];
                    if(arrayData.table){
                        element.style.backgroundColor="yellow";
                    }else{
                        element.setAttribute("disabled",true);
                    }
                    element.addEventListener("click",function(){                        
                        if(confirm('Migrate down akan dilakukan?')){
                            let index = element.getAttribute("index");
                            let arrayData = data[index];
                            var url = "{{url('laradev/migrate')}}/"+(arrayData.file).replace(".php","")+"?down=true";
                            submitApi({
                                url : url,
                                method: "get",
                                body:null
                            },function(response){
                                // codemirror.setValue(response.data.text);
                                window.location.reload();
                                // console.log(response);
                            });
                        }
                    });
                });
            </script>
    </body>
</html>