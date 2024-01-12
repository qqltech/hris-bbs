<html>
    <head>
        <title>BACKEND - Larahan</title>
        <link rel="icon" href="{{url('favicon.ico')}}">
        <link rel="stylesheet" href="{{url('defaults/request2.css')}}">
        <link rel="stylesheet" href="{{url('defaults/codemirror.css')}}">
        <link rel="stylesheet" href="{{url('defaults/theme/monokai.css')}}">
        <link rel="stylesheet" href="{{url('defaults/addon/lint/lint.css')}}">
        <link rel="stylesheet" href="{{url('defaults/addon/hint/show-hint.css')}}">
        <style>
            #codemirror{
                width:60%;
                bottom:0px !important;
                transition: all .23s linear;
            }
            
            .CodeMirror{
                z-index: 50;
                height:100% !important;
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
        <form id="form" method="POST" action="{{$data['url']}}" style="display:none">
            <input id="password" type="hidden" name="password" value="{{$data['password']}}">
        </form>
        <script>
            function onsubmit(){
                localStorage.scrollY=window.scrollY;
            };

        </script>
        <p><span style="padding:0 20px 5 20px;position:fixed;right:40px;top:0px;font-weight:bold;background-color:green;color:white" id="modelSelected"></span>
            <button style="position:fixed;right:0px;top:0px; background-color:red;color:white" id="toggle">Hide!</button>
        </p>
        <button style="position:fixed;right:0px;bottom:3px; background-color:green;color:white;z-index:51" id="toggle_full">full!</button>
        <div id="codemirror">
            <textarea id="code" style="display:none"></textarea>
        </div>
        <div>
            <table border="1">
                <thead>
                    <th>Migrations<button id="new" style="background-color:greenyellow">+</button></th>
                    <th colspan="8">Actions <button id="real_fk" style="background-color:greenyellow">Set FK</button><button id="drop_fk" style="background-color:pink">Drop ({{$realfk}}) FK</button></th>
                </thead>
                <tbody>
                    @foreach($models as $key => $model)
                        @if( !(strpos($model['file'], 'oauth') !== false))
                            <tr>
                                <td style="padding:0 5 0 5;font-size:13.5px" id="data-{{$key}}">{{ str_replace(".php","",$model['file'])}}</td>
                                <td><button class="alter" href="javascript:void(0)" style="font-size:10px" index={{$key}}>Alter</button></td>
                                <td><button class="migration" href="javascript:void(0)" style="font-size:10px" index={{$key}}>Migration</button></td>
                                <td><button class="model" href="javascript:void(0)" style="font-size:10px;"  index={{$key}}>Model</button></td>
                                <td><button class="migrate" href="javascript:void(0)" style="font-size:10px" index={{$key}}>UP @if($model['alias'])<button class="refreshalias" href="javascript:void(0)" style="font-size:10px" index={{$key}}>REF</button> @endif</button></td>
                                <td><button class="down" href="javascript:void(0)" style="font-size:10px" index={{$key}}>DROP</button></td>
                                <td><button class="alt" href="javascript:void(0)" style="font-size:10px" index={{$key}}>ALT</button></td>
                                <td><button class="rename" href="javascript:void(0)" style="font-size:10px" 
                                    @if( strpos($model['file'],"_after_" ) !==false || strpos($model['file'],"_before_" ) !==false || (strpos($model['file'], 'default_') !== false || $model['alias']) ) disabled @endif
                                    index={{$key}}>Rename</button></td>
                                <td><button class="delete" href="javascript:void(0)" style="font-size:10px" 
                                    @if( (strpos($model['file'], 'default_') !== false) ) disabled @endif
                                    index={{$key}}>[X]</button></td>
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
                            laradev:"{{env('LARADEVPASSWORD','bismillah')}}"
                        }
                    }
                    if(data.method.toLowerCase() == "get"){
                        $options["params"] = data.body;
                    }
                    axios($options).then(response => {
                        window.console.clear();
                        let operationText  = document.getElementById("modelSelected").innerText;
                        if(operationText.includes("MIGRATION") || operationText.includes("ALTER")|| operationText.includes("MODEL")){
                            console.log(`%c ${operationText} OK`, 'color: green');
                        }else{
                            console.log(response.data);
                        }
                        callback(response);
                    }).catch(error => {
                        window.console.clear();
                        if(error.response.data=='nopassword'){
                            var password = prompt("file telah dipassword, masukkan password:", "");
                            if (password == null || password == "") {
                            }else{
                                let url = (error.config.url).split("?")['0'];
                                submitApi({
                                    url : url+"?password="+password,
                                    method: "get",
                                    body:null
                                },function(response){
                                    if(url.includes('laradev/models')){
                                        codemirror.setValue(response.data.text);
                                    }
                                });                                
                            }
                        }else{
                            alert("gagal, lihat console!");
                            throw(error.response.data);
                        }
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
                            onsubmit();
                            document.getElementById("form").submit();
                            // console.log(response);
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
                            onsubmit();
                            document.getElementById("form").submit();
                            // console.log(response);
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
                            onsubmit();
                            document.getElementById("form").submit();
                            // console.log(response);
                        });
                    }
                });

                var data = @php echo json_encode($models); @endphp;
                var codemirror = CodeMirror.fromTextArea(document.getElementById("code"), {
                    lineNumbers: true,
                    firstLineNumber:1,
                    foldGutter: true,
                    gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
                    lineWrapping: true,
                    mode: "php",
                    viewportMargin: Infinity,
                    theme:"monokai",
                    keyMap:"sublime",
                    matchBrackets: true,
                    continueComments: "Enter",
                    lint: true,
                  });
                    var map = {"Ctrl-S": function(cm){
                        let operationText  = document.getElementById("modelSelected").innerText;
                        let file = operationText.split(".php")[0];
                        let operation = "";
                        if(operationText.includes("ALTER")){
                            operation = "alter";
                        }else if(operationText.includes("MODEL")){
                            operation = "models";
                        }else if(operationText.includes("MIGRATION")){
                            operation = "migrations";
                        }
                        var valueText = cm.getValue();
                        var currentData = currentmodel==null?currentmigration:currentmodel;
                        var url = `{{url('laradev')}}/${operation}/${file}`;
                        submitApi({
                            url : url,
                            method: "put",
                            body:{
                                text : valueText
                            }
                        },function(response){
                            // console.log(response);
                            alert(`${operation} berhasil tersimpan`);
                        });

                    }}
                    codemirror.addKeyMap(map);
                    codemirror.setOption("extraKeys", {
                        "Ctrl-O": cm => CodeMirror.commands.foldAll(cm),
                        "Ctrl-I": cm => CodeMirror.commands.unfoldAll(cm),
                        "Ctrl-Q": cm => cm.foldCode(cm.getCursor())
                    })
                    // if(localStorage.valueText!=undefined){
                    //     codemirror.setValue(localStorage.valueText);
                    // }

                var isToggled=true;
                document.getElementById("codemirror").style.display = "none";
                document.getElementById("modelSelected").style.display = "none";
                var isFull = false;
                document.getElementById("toggle_full").onclick = function(){
                    if(isFull){
                        document.getElementById("codemirror").style.width="60"+"%";
                        document.getElementsByClassName('CodeMirror-hscrollbar')[0].style.display="block";
                        isFull = false;
                    }else{
                        document.getElementById("codemirror").style.width="98"+"%";
                        document.getElementsByClassName('CodeMirror-hscrollbar')[0].style.display="none";
                        isFull = true;
                    }
                };

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
                    
                    if(e.altKey && e.key=="Enter"){
                        document.getElementById('toggle_full').click();
                    }
                });
                
                var classname = document.getElementsByClassName("refreshalias");
                Array.from(classname).forEach(function(element) {                                        
                    let index = element.getAttribute("index");
                    let arrayData = data[index];
                    element.addEventListener("click",function(e){
                        document.getElementById(`data-${index}`).style.backgroundColor = "red";
                        document.getElementById(`data-${index}`).style.color = "white";                        
                        setTimeout(function() {
                            var confirmation = confirm(`REFRESH ALIAS [${(arrayData.file).replace(".php","")}]?`);
                            if (!confirmation) {   
                                document.getElementById(`data-${index}`).style.backgroundColor = "white";
                                document.getElementById(`data-${index}`).style.color = "black";
                            } else {
                                var url = "{{url('laradev/refreshalias')}}/"+(arrayData.file).replace(".php","");
                                submitApi({
                                    url : url,
                                    method: "get",
                                    body:null
                                },function(response){
                                    alert("refresh Alias Successfully")
                                    document.getElementById(`data-${index}`).style.backgroundColor = "white";
                                    document.getElementById(`data-${index}`).style.color = "black";
                                });
                            }
                        },1);
                    });
                });
                var classname = document.getElementsByClassName("alter");
                Array.from(classname).forEach(function(element) {                                        
                    let index = element.getAttribute("index");
                    let arrayData = data[index];
                    if(!arrayData.model && !arrayData.alias ){
                        element.style.backgroundColor="red";
                    }
                    if( (arrayData.file).includes("_after_") || (arrayData.file).includes("_before_") || arrayData.table===false || arrayData.view===true){
                        element.style.display="none";
                    }
                    element.addEventListener("click",function(){
                        var url = "{{url('laradev/alter')}}/"+(arrayData.file).replace(".php","");
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
                            document.getElementById(`data-${index}`).style.backgroundColor = "brown";
                            document.getElementById(`data-${index}`).style.color = "white";
                            lastid=index;

                        });
                        document.getElementById("modelSelected").innerText=arrayData.file+" [ALTER]";
                        document.getElementById("codemirror").style.display = "block";
                        isToggled = false;
                        document.getElementById("modelSelected").style.display = "block";
                    });
                });

                var classname = document.getElementsByClassName("migration");
                Array.from(classname).forEach(function(element) {                    
                    let index = element.getAttribute("index");
                    let arrayData = data[index];
                    if( (arrayData.file).includes("_after_") || (arrayData.file).includes("_before_")){
                        element.innerHTML="&nbsp;"+"&nbsp;"+"Trigger"+"&nbsp;";
                    }else if(arrayData.alias){
                        element.innerHTML="&nbsp;"+"&nbsp;"+"&nbsp;"+"&nbsp;"+"Alias"+"&nbsp;"+"&nbsp;";
                    }else if(arrayData.view){
                        element.innerHTML="&nbsp;"+"&nbsp;"+"&nbsp;"+"&nbsp;"+"View"+"&nbsp;"+"&nbsp;";
                    }
                    element.addEventListener("click",function(){
                        
                        
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
                        isToggled = false;
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
                        isToggled = false;
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
                    if( arrayData.alias){
                        element.style.display="none";
                    }
                    element.addEventListener("click",function(){                       
                        let index = element.getAttribute("index");
                        let arrayData = data[index];
                        document.getElementById(`data-${index}`).style.backgroundColor = "red";
                        document.getElementById(`data-${index}`).style.color = "white";
                        let isTrue;
                        setTimeout(function() {
                            isTrue = confirm('[MIGRATE:refresh] Table dan BasicModel akan ter-replace?'); 
                            if(isTrue){
                                var url = "{{url('laradev/migrate')}}/"+(arrayData.file).replace(".php","");
                                submitApi({
                                    url : url,
                                    method: "get",
                                    body:null
                                },function(response){
                                    // codemirror.setValue(response.data.text);
                                    onsubmit();
                                    document.getElementById("form").submit();
                                    // console.log(response);
                                });
                            }else{
                                document.getElementById(`data-${index}`).style.backgroundColor = "white";
                                document.getElementById(`data-${index}`).style.color = "black";
                            }
                        },1);
                    });
                });

                
                var classname = document.getElementsByClassName("alt");
                Array.from(classname).forEach(function(element) {
                    
                    let index = element.getAttribute("index");
                    let arrayData = data[index];
                    if(!arrayData.table || arrayData.alias){
                        element.style.display="none";
                    }

                    if( (arrayData.file).includes("_after_") || (arrayData.file).includes("_before_") || arrayData.table===false || arrayData.view===true){
                        element.style.display="none";
                    }
                    element.addEventListener("click",function(){                       
                        let index = element.getAttribute("index");
                        let arrayData = data[index];
                        document.getElementById(`data-${index}`).style.backgroundColor = "red";
                        document.getElementById(`data-${index}`).style.color = "white";
                        let isTrue;
                        setTimeout(function() {
                            isTrue = confirm('[ALTER] Table dan BasicModel akan ter-update?'); 
                            if(isTrue){
                                var url = "{{url('laradev/migrate')}}/"+(arrayData.file).replace(".php","")+"?alter=true";
                                submitApi({
                                    url : url,
                                    method: "get",
                                    body:null
                                },function(response){
                                    // codemirror.setValue(response.data.text);
                                    onsubmit();
                                    document.getElementById("form").submit();
                                    // console.log(response);
                                });
                            }else{
                                document.getElementById(`data-${index}`).style.backgroundColor = "white";
                                document.getElementById(`data-${index}`).style.color = "black";
                            }
                        },1);
                    });
                });

                var classname = document.getElementsByClassName("delete");
                Array.from(classname).forEach(function(element) {
                    element.addEventListener("click",function(){
                        let index = element.getAttribute("index");
                        let arrayData = data[index];
                        document.getElementById(`data-${index}`).style.backgroundColor = "red";
                        document.getElementById(`data-${index}`).style.color = "white";                        
                        setTimeout(function() {
                            var password = prompt(`[${(arrayData.file).replace(".php","")}] Migration, Model, Table akan hilang!, password:`, "");
                            if (password == null || password == "") {   
                                document.getElementById(`data-${index}`).style.backgroundColor = "white";
                                document.getElementById(`data-${index}`).style.color = "black";
                            } else {
                                var url = "{{url('laradev/trio')}}/"+(arrayData.file).replace(".php","");
                                submitApi({
                                    url : url,
                                    method: "post",
                                    body:{
                                        password : password
                                    }
                                },function(response){
                                    onsubmit();
                                    document.getElementById("form").submit();
                                });
                            }
                        },1);
                    });
                });
                var classname = document.getElementsByClassName("rename");
                Array.from(classname).forEach(function(element) {
                    element.addEventListener("click",function(){                        
                        let index = element.getAttribute("index");
                        let arrayData = data[index];
                        document.getElementById(`data-${index}`).style.backgroundColor = "red";
                        document.getElementById(`data-${index}`).style.color = "white";
                        setTimeout(function() {
                            var table = prompt((arrayData.file).replace(".php","")+" ->New Migration Name:", "");
                            if (table == null || table == "") {                                    
                                document.getElementById(`data-${index}`).style.backgroundColor = "white";
                                document.getElementById(`data-${index}`).style.color = "black";
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
                                    onsubmit();
                                    document.getElementById("form").submit();
                                });
                            }
                        },1);  
                        
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
                    if( arrayData.alias){
                        element.style.display="none";
                    }
                    element.addEventListener("click",function(){                        
                        document.getElementById(`data-${index}`).style.backgroundColor = "red";
                        document.getElementById(`data-${index}`).style.color = "white";
                        let isTrue;
                        setTimeout(function() {
                            isTrue = confirm('Migrate down akan dilakukan?'); 
                            if(isTrue){
                                let index = element.getAttribute("index");
                                let arrayData = data[index];
                                var url = "{{url('laradev/migrate')}}/"+(arrayData.file).replace(".php","")+"?down=true";
                                submitApi({
                                    url : url,
                                    method: "get",
                                    body:null
                                },function(response){
                                    // codemirror.setValue(response.data.text);
                                    onsubmit();
                                    document.getElementById("form").submit();
                                    // console.log(response);
                                });
                            }else{
                                document.getElementById(`data-${index}`).style.backgroundColor = "white";
                                document.getElementById(`data-${index}`).style.color = "black";
                            }
                        },1);
                    });
                });

                var ws = new WebSocket("wss://backend.dejozz.com:9001/{{env('LOG_CHANNEL',"+btoa(window.location.host)+")}}");
				
                ws.onopen = function() {
                    console.log("%c debug is ready to use","background: #222; color: #a0ff5c;font-weight: bold;");
                };

                ws.onmessage = function (evt) { 
                    var received_msg = evt.data;
                    try{
                        received_msg=JSON.parse(received_msg);
                        console.log("%c "+received_msg.debug_id,"background: #222; color: #a0ff5c;font-weight: bold;",received_msg);
                    }catch(e){
                        if(received_msg.includes('bc ')){
                            alert(received_msg.replace("bc ",""))
                        }
                        console.log(received_msg);
                    }
                };

                ws.onclose = function() {
                    console.log("connection is closed");
                };
                document.addEventListener('DOMContentLoaded', ()=>{
                    if(localStorage.scrollY!==undefined){
                        window.scrollTo({
                            top: localStorage.scrollY,
                            left: 0,
                            behavior: 'smooth'
                        });
                    }
                }, false);
            </script>
    </body>
</html>