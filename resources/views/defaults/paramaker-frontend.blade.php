<html>
<title>PREPARED QUERY</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="{{url('defaults/vue.min.js')}}"></script>
        <script src="{{url('defaults/axios.min.js')}}"></script>
<script src="https://unpkg.com/windicss-runtime-dom"></script>
<body class="block" hidden>
@verbatim
    <div class="w-screen min-h-screen !text-xs" id="app">
        <table class="w-full max-h-full overflow-y-auto px-2" cellpadding="0" cellspacing="0">
            <thead class="bg-gray-800 text-white !text-xs">
                <th class="p-2 border-r">Api Endpoint</th>
                <th class="p-2 border-r">Query Name</th>
                <th class="p-2 border-r">Keterangan</th>
                <th class="p-2 border-r">Parameters</th>
                <th class="p-2 border-r">Prepared Query Statement</th>
                <th class="p-2 border-r">Active</th>
            </thead>
            <tbody>
                <tr v-for="(row,idx) in rows" class="border" :key="'row'+idx">
                    <td class="p-1 w-1/8 relative">
                        <input type='text' readonly @focus="focusIdx=idx"  v-model="rows[idx].modul" class="w-full bg-gray-100 focus:bg-white border p-2">
                    </td>
                    <td class="p-1 w-1/8 relative">
                        <input type='text' readonly @focus="focusIdx=idx"  v-model="rows[idx].name" class="w-full bg-gray-100 focus:bg-white border p-2">
                    </td>
                    <td class="p-1 w-1/4"><input type='text' v-model="rows[idx].note" readonly @focus="focusIdx=idx"  class="w-full bg-gray-100 focus:bg-white border p-2"></td>
                    <td class="p-1">
                        <textarea 
                            v-model="rows[idx].params" style="resize:vertical;" readonly @focus="focusIdx=idx"
                            class="min-w-full bg-gray-100 focus:bg-white border p-2 h-8.5 transition-all duration-300" 
                            :class="{'!h-40' : focusIdx===idx}"
                            rows="12">
                        </textarea>
                    </td>
                    <td class="p-1">
                        <textarea 
                            v-model="rows[idx].prepared_query" style="resize:vertical;" readonly @focus="focusIdx=idx"
                            class="min-w-full bg-gray-100 focus:bg-white border p-2 h-8.5 transition-all duration-300" 
                            :class="{'!h-40' : focusIdx===idx}"
                            rows="12">
                        </textarea>
                    </td>
                    <td class="p-1 w-60px text-center">
                        <div class="p-1 w-full text-center flex flex-row items-center justify-center text-center">
                            <input type='checkbox' 
                                disabled
                                class="cursor-pointer" 
                                v-model="rows[idx]['is_active']">
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        </div>
   
@endverbatim
<script>
    var app = new Vue({
        el: '#app',
        data: {
            rows:{},
            focusIdx:null
        },
        mounted(){
            var list = {!!$list!!};
            this.rows=list;
        }
    })
</script>
</body>
</html>