<html>
    <head>
        <title>OPERATION - Larahan</title>
        <link rel="icon" href="{{url('favicon.ico')}}">
        <link rel="stylesheet" href="defaults/request.css">
        <link rel="stylesheet" href="defaults/codemirror.css">
        <link rel="stylesheet" href="defaults/theme/monokai.css">
        <link rel="stylesheet" href="defaults/addon/lint/lint.css">
    </head>
    <body>
<input type=hidden id="urlCurrent" value="{{url()}}"">
<div id="codemirror">
    <p class="judul" id="judul">REQUEST</p>
    <p class="endpoint" id="url"></p>
    <textarea id="code">
    </textarea>
    </p><a href="javascript:void(0)" class="button" id="run">Run on Console!</a></p>
</div>

<div>
    <p class="title">LOGIN</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/login",
    "method"    : "POST",
    "headers"   :{
    },
    "body"  : {
        "email" : "trial@trial.trial",
        "password" : "trial"
    }
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>

<div>
    <p class="title">GET ME</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/me",
    "method"    : "GET",
    "headers"   :{
        "authorization"  : "token setelah login",
        "Cache-Control" : "no-cache"
    },
    "body"  : {}
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>

<div>
    <p class="title">CREATE NORMAL</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/operation/model",
    "method"    : "POST",
    "headers"   :{
        "authorization"  : "token setelah login",
        "Cache-Control" : "no-cache"
    },
    "body": {
		"color": "datakolom1",
		"draft_no": "datakolom2",
		"inv_tra_material_transfer_d_item": [
			{
				"no_pendaftaran": "hal12o",
				"kolomdetail2": "halo",
				"inv_tra_material_transfer_d_item_d_other": [
					{
						"color": "22",
						"kolomsubdetail2": 2
					}
				]
			}
		]
	}
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>

<div>
    <p class="title">CREATE MASS</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/operation/model",
    "method"    : "POST",
    "headers"   :{
        "authorization"  : "token setelah login",
        "Cache-Control" : "no-cache"
    },
    "body": 
        [{
        "kolom1": "datakolom1",
        "kolomh2": "datakolom2",
        "modeldetail_1": [
                {
                "kolomdetail1":"halo",
                "kolomdetail2":"halo",
                "modelsubdetail_1":[
                    {
                    "kolomsubdetail1":1,
                    "kolomsubdetail2":2
                    }  
                ]
                }        
            ],
        "modeldetail2_":[
                {
                "kolomdetail2":"data"
                }
            ]       
     
    }]
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>

<div>
    <p class="title">CREATE DETAILS</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/operation/model/1/modeldetail",
    "method"    : "POST",
    "headers"   :{
        "authorization"  : "token setelah login",
        "Cache-Control" : "no-cache"
    },
    "body": {
      "kolom1": "datakolom1",
      "kolomh2": "datakolom2",
      "modeldetail_1": [
            {
              "kolomdetail1":"halo",
              "kolomdetail2":"halo",
              "modelsubdetail_1":[
                {
                  "kolomsubdetail1":1,
                  "kolomsubdetail2":2
                }  
              ]
            }        
          ],
      "modeldetail2_":[
            {
              "kolomdetail2":"data"
            }
          ]
    }
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>

<div>
    <p class="title">UPDATE</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/operation/model/1",
    "method"    : "PUT",
    "headers"   :{
        "authorization"  : "token setelah login",
        "Cache-Control" : "no-cache"
    },
    "body": {
		"color": "datakolom1",
		"draft_no": "datakolom2",
		"inv_tra_material_transfer_d_item": [
			{
				"no_pendaftaran": "hal12o",
				"kolomdetail2": "halo",
				"inv_tra_material_transfer_d_item_d_other": [
					{
						"color": "22",
						"kolomsubdetail2": 2
					}
				]
			}
		]
	}
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>

<div>
    <p class="title">DELETE</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/operation/model/1",
    "method"    : "DELETE",
    "headers"   :{
        "authorization"  : "token setelah login",
        "Cache-Control" : "no-cache"
    },
    "body": {
    }
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>


<div>
    <p class="title">READ</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/operation/model",
    "method"    : "GET",
    "headers"   :{
        "authorization"  : "token setelah login",
        "Cache-Control" : "no-cache"
    },
    "body": {
        "paginate"    : 25,
        "order_by"     : "id",
        "order_type"   : "asc"
    }
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>

<div>
    <p class="title">CUSTOM FUNC GET</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/custom/model/nama_function",
    "method"    : "GET",
    "headers"   :{
        "authorization"  : "token setelah login",
        "Cache-Control" : "no-cache"
    },
    "body": {
        "params1"    : "ini param",
        "params2"    : "ini param"
    }
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>

<div>
    <p class="title">CUSTOM FUNC POST</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/custom/model/nama_function",
    "method"    : "POST",
    "headers"   :{
        "authorization"  : "token setelah login",
        "Cache-Control" : "no-cache"
    },
    "body": {
        "params1"    : "ini param",
        "params2"    : "ini param"
    }
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>





        <script src="defaults/axios.min.js"></script>
        <script src="defaults/codemirror.js"></script>
        <script src="defaults/addon/mode/loadmode.js"></script>
        <script src="defaults/addon/mode/javascript.js"></script>
        <script src="defaults/addon/search/searchcursor.js"></script>
        <script src="defaults/addon/search/search.js"></script>
        <script src="defaults/addon/dialog/dialog.js"></script>
        <script src="defaults/addon/edit/matchbrackets.js"></script>
        <script src="defaults/addon/edit/closebrackets.js"></script>
        <script src="defaults/addon/comment/comment.js"></script>
        <script src="defaults/addon/wrap/hardwrap.js"></script>
        <script src="defaults/addon/fold/foldcode.js"></script>
        <script src="defaults/addon/fold/brace-fold.js"></script>
        <script src="defaults/addon/keymaps/sublime.js"></script>
        <script src="defaults/addon/edit/matchbrackets.js"></script>
        <script src="defaults/addon/comment/continuecomment.js"></script>
        <script src="defaults/addon/comment/comment.js"></script>
        <script src="defaults/addon/lint/jshint.js"></script>
        <script src="defaults/addon/lint/lint.js"></script>
        <script src="defaults/addon/lint/javascript-lint.js"></script>
        <script src="defaults/addon/lint/css-lint.js"></script>
        <script src="defaults/request.js"></script>
        <script src="defaults/autosize.js"></script>
        <script>
            alert("Buka browser console untuk melihat response.")
        </script>
    </body>
</html>