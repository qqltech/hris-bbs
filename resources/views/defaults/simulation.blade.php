<html>
<head>
    <title> SIMULATION </title>
    <link rel="icon" href="{{url('favicon.ico')}}">
    <script src="{{url('defaults/vue.min.js')}}"></script>
    <link rel="stylesheet" href="{{url('defaults/tailwind.min.css')}}">
@verbatim
</head>
<body>
<div class="container">
    <div id="app">
    <table border=1 style="padding:2px;width:100%">
        <thead><th style="width:5%">&nbsp;Nomor&nbsp;</th><th style="width:15%">&nbsp;Time(ms)&nbsp;</th><th>&nbsp;Process&nbsp;</th></thead>
        <tbody>
            <tr v-for="(item, index) in process" style="padding:2px;">
                <td style="text-align:center">{{index+1}}</td>
                <td style="text-align:center">{{item.time}}</td>
                <td style="padding:10px">{{item.process}}</td>
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
        process:[{
            process:'process1',
            time:"120"
        },{
            process:'process2',
            time:"120"
        }]
    },
    created(){        
        var channel = prompt(`Channel SOCKET (sesuai variable: public $socketChannel di Custom Model`, "");
        if (channel == null || channel == "") {
        }else {
            this.channel=channel;
            document.title='SIMULATION : '+channel;          
        }
        
        var ws = new WebSocket("wss://backend.dejozz.com:9001/"+channel);

        ws.onopen = function() {
            alert('socket telah terkoneksi ke channel '+channel+', tinggal menunggu data!')
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
    },
    methods: {
        test(){return 'abc';}
    }
})
</script>
</body>
</html>