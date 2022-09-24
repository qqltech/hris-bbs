<html>
    <head>
        <title>CONFIG - Larahan</title>
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
    <p class="title">ENVIRONMENT READ</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/laradev/environment",
    "method"    : "GET",
    "headers"   :{
        "laradev"   : "bismillah",
        "Cache-Control" : "no-cache"
    },
    "body"  : {
    }
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>

<div>
    <p class="title">ENVIRONMENT CREATE</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/laradev/environment",
    "method"    : "PUT",
    "headers"   :{
        "laradev"   : "bismillah",
        "Cache-Control" : "no-cache"
    },
    "body"  : 
    @php 
        $data = [
            'APP_DEBUG' => 'true',
            'APP_ENV' => 'local',
            'APP_KEY' => 'base64:C4zyfJxLlJ8nxA6y6ENFK3qsq9fghPqscFaSr2wB7Uc=',
            'APP_NAME' => 'Project',
            'APP_TIMEZONE' => 'Asia/Jakarta',
            'LOCALE' => 'EN',
            'QUEUE_CONNECTION' => 'database',
            'APP_URL' => 'http://localhost',
            'AUTHORIZATION' => 'true',
            'BACKENDPASSWORD' =>'bismillah',
            'CONFIGPASSWORD' =>'bismillah',
            'LARADEVPASSWORD' =>'bismillah',
            'SERVERSTATUS' => 'OPEN',
            'DB_CONNECTION' => 'mysql',
            'DB_DATABASE' => 'trial2',
            'DB_HOST' => 'localhost',
            'DB_PORT' => '3306',
            'DB_USERNAME' => 'root',
            'DB_PASSWORD' => '',
            'DB_STRICT_MODE' => 'false',
            'MAIL_DRIVER' => 'smtp',
            'MAIL_ENCRYPTION' => 'ssl',
            'MAIL_FROM_ADDRESS' => 'starlight93@gmail.com',
            'MAIL_FROM_NAME' => 'fajar',
            'MAIL_HOST' => 'smtp.googlemail.com',
            'MAIL_PASSWORD' => '',
            'MAIL_PORT' => '465',
            'MAIL_USERNAME' => 'starlight93@gmail.com',
            'TG_TOKEN' => 'xxx',
            'TG_CHATID' => 'xxx',
            'LOG_CHANNEL' => '777',
            'SINGLE_LOGIN' => 'true',
            'FORMAT_DATE_FRONTEND' => 'd/m/Y',
            'FIREBASE_KEY' => 'xxx',
            'GIT_ENABLE'=>'false',
            'GIT_PUSH_START'=>'16:00',
            'GIT_URL'=>'https://larahan:larahansuperuser2019@gitlab.com/exampleproject',  
        ];
        $env = [];
        foreach ($data as $key => $value) {
            $env[$key] = urldecode(getenv($key));
        };
            echo json_encode($env, JSON_PRETTY_PRINT);
    @endphp    
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>
{{-- 
<div>
    <p class="title">DATABASES FRESH CONFIGURATION</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/laradev/databases",
    "method"    : "POST",
    "headers"   :{
        "laradev"   : "bismillah",
        "Cache-Control" : "no-cache"
    },
    "body"  : {
        "db_migrate" : false,
        "db_fresh"   : true,
        "db_seed"    : true,
        "db_passport": true

    }
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div> --}}

<div>
    <p class="title">DATABASES READ or CREATE</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/laradev/databases",
    "method"    : "GET",
    "headers"   :{
        "laradev"   : "bismillah",
        "Cache-Control" : "no-cache"
    },
    "body"  : {
        "db_autocreate":false,
        "db_migrate" : false,
        "db_fresh"   : true,
        "db_seed"    : true
    }
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>


<div>
    <p class="title">DATABASES DELETE @database</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/laradev/databases/nama_database",
    "method"    : "DELETE",
    "headers"   :{
        "laradev"   : "bismillah",
        "Cache-Control" : "no-cache"
    },
    "body"  : {}
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>

<div>
    <p class="title">TABLES READ</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/laradev/tables",
    "method"    : "GET",
    "headers"   :{
        "laradev"   : "bismillah",
        "Cache-Control" : "no-cache"
    },
    "body"  : {
        "details" : true
    }
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>

<div>
    <p class="title">TABLE READ @table</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/laradev/tables/default_users",
    "method"    : "GET",
    "headers"   :{
        "laradev"   : "bismillah",
        "Cache-Control" : "no-cache"
    },
    "body"  : {
    }
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>

<div>
    <p class="title">TABLE RENAME @table</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/laradev/tables/mytable",
    "method"    : "PUT",
    "headers"   :{
        "laradev"   : "bismillah",
        "Cache-Control" : "no-cache"
    },
    "body"  : {
        "name" : "new_table_name",
        "models" : true
    }
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>

<div>
    <p class="title">TABLE CREATE/UPDATE @table</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/laradev/tables",
    "method"    : "POST",
    "headers"   :{
        "laradev"   : "bismillah",
        "Cache-Control" : "no-cache"
    },
    "body"  : {
        "table":"mytable",
        "columns":[
            {
                "datatype":"integer",
                "name"    : "nilai"
            },
            {
                "datatype":"text",
                "name"    : "nama"
            },{
                "datatype": "unsignedInteger",
                "name"    : "user_id",
                "meta": {
                    "fk":"item_detail.id",
                    "required":true
                }
            }
          ]
    }
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>

<div>
    <p class="title">TABLE DELETE @table</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/laradev/tables/mytable",
    "method"    : "DELETE",
    "headers"   :{
        "laradev"   : "bismillah",
        "Cache-Control" : "no-cache"
    },
    "body"  : {
        "models" : true
    }
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>

<div>
    <p class="title">TABLE UPDATE/CREATE TRIGGER @table</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/laradev/tables/mytable/trigger",
    "method"    : "PUT",
    "headers"   :{
        "laradev"   : "bismillah",
        "Cache-Control" : "no-cache"
    },
    "body"  : {
        "script" : "NEW.name='new';",
        "time"   : "after",
        "event"  : "insert"
    }
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>

<div>
    <p class="title">TABLE DELETE TRIGGER @table</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/laradev/tables/mytable/trigger",
    "method"    : "DELETE",
    "headers"   :{
        "laradev"   : "bismillah",
        "Cache-Control" : "no-cache"
    },
    "body"  : {
        "time"   : "after",
        "event"  : "insert"
    }
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>


<div>
    <p class="title">MIGRATION DEFAULT</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/laradev/migrate",
    "method"    : "POST",
    "headers"   :{
        "laradev"   : "bismillah",
        "Cache-Control" : "no-cache"
    },
    "body"  : {
    	"fresh":true,
        "seed":true,
        "passport":true
    }
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>

<div>
    <p class="title">MODELS READ</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/laradev/models",
    "method"    : "GET",
    "headers"   :{
        "laradev"   : "bismillah",
        "Cache-Control" : "no-cache"
    },
    "body"  : {
    }
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>

<div>
    <p class="title">MODELS READ @model</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/laradev/models/default_users",
    "method"    : "GET",
    "headers"   :{
        "laradev"   : "bismillah",
        "Cache-Control" : "no-cache"
    },
    "body"  : {
        "script_only":true
    }
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>

<div>
    <p class="title">MODELS CREATE ALL TABLES</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/laradev/models",
    "method"    : "POST",
    "headers"   :{
        "laradev"   : "bismillah",
        "Cache-Control" : "no-cache"
    },
    "body"  : {
        "rewrite_custom": false,
        "fresh"         : false
    }
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>

<div>
    <p class="title">MODEL UPDATE SCRIPT @model</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/laradev/models/default_users",
    "method"    : "PUT",
    "headers"   :{
        "laradev"   : "bismillah",
        "Cache-Control" : "no-cache"
    },
    "body"  : {
        "text"  :"awef"
    }
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>

<div>
    <p class="title">MODEL CREATE @table</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/laradev/models/default_users",
    "method"    : "POST",
    "headers"   :{
        "laradev"   : "bismillah",
        "Cache-Control" : "no-cache"
    },
    "body"  : {
        "rewrite_custom": false
    }
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>

<div>
    <p class="title">MIGRATION LIST @table</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/laradev/migrations/migration_name",
    "method"    : "GET",
    "headers"   :{
        "laradev"   : "bismillah",
        "Cache-Control" : "no-cache"
    },
    "body"  : {
    }
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>

<div>
    <p class="title">MIGRATION CREATE @table</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/laradev/migrations",
    "method"    : "POST",
    "headers"   :{
        "laradev"   : "bismillah",
        "Cache-Control" : "no-cache"
    },
    "body"  : {
        "modul": "inv_tra_material_transfer"
    }
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>

<div>
    <p class="title">DO MIGRATE @table</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/laradev/migrate/table",
    "method"    : "GET",
    "headers"   :{
        "laradev"   : "bismillah",
        "Cache-Control" : "no-cache"
    },
    "body"  : {
        "rewrite_custom": false
    }
}
    </textarea>
    </p><button href="javascript:void(0)">Copy to Editor</button></p>
</div>

<div>
    <p class="title">EMAIL SEND</p>
    <textarea class="samplecode" readonly>
{
    "url"       : "/laradev/mail",
    "method"    : "POST",
    "headers"   :{
        "laradev"   : "bismillah",
        "Cache-Control" : "no-cache"
    },
    "body"  : {
        "email"     : "maill.firmansyah93@gmail.com",
        "name"      : "dev"
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
    </body>
</html>