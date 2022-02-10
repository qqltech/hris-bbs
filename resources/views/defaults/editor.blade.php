<html>
<head>
    <title> EDITOR </title>
    <link rel="icon" href="{{url('favicon.ico')}}">
    <script src="//cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ext-language_tools.min.js" integrity="sha512-8qx1DL/2Wsrrij2TWX5UzvEaYOFVndR7BogdpOyF4ocMfnfkw28qt8ULkXD9Tef0bLvh3TpnSAljDC7uyniEuQ==" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="{{url('defaults/vue.min.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue-loading-overlay@3"></script>
    <link href="https://cdn.jsdelivr.net/npm/vue-loading-overlay@3/dist/vue-loading.css" rel="stylesheet">
    <!-- <script src="https://unpkg.com/vue-select@3.0.0"></script>
    <link rel="stylesheet" href="https://unpkg.com/vue-select@3.0.0/dist/vue-select.css"> -->
    <script src="{{url('defaults/axios.min.js')}}"></script>
    <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap@4.6.0/dist/css/bootstrap.min.css" />
    <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.min.css" />
    <script src="//unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue-splitpane@1.0.6/dist/vue-split-pane.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vuex/2.1.1/vuex.min.js"></script>
    <style>
    .nav-tabs {
        flex-wrap: nowrap;
        white-space: nowrap;
        overflow-x: auto;
        overflow-y: hidden;
        scroll-behavior: smooth;
    }
        .ace-monokai .ace_marker-layer .ace_active-line {
            /* background: #49483E; */
        }
        .ace-monokai .ace_marker-layer .ace_selected-word {
            background: #6e6c5c !important;
        }
        .no-select {
            -webkit-touch-callout: none; /* iOS Safari */
                -webkit-user-select: none; /* Safari */
                -khtml-user-select: none; /* Konqueror HTML */
                -moz-user-select: none; /* Old versions of Firefox */
                    -ms-user-select: none; /* Internet Explorer/Edge */
                        user-select: none; /* Non-prefixed version, currently
                                            supported by Chrome, Edge, Opera and Firefox */
            }
        .ace-monokai .ace_string {
            /* color: #f5e658 !important; */
        }
        .ace-monokai .ace_gutter {
            
            background: #27282236 !important;
            /* background: #1e1f1c !important; */
            color: #8F908A;
        }
        .ace_prompt_container {
            z-index:999999999 !important;
        }
        .ace-monokai .ace_entity.ace_name.ace_tag, .ace-monokai .ace_keyword, .ace-monokai .ace_meta.ace_tag, .ace-monokai .ace_storage {
            color: #F92672;
            /* font-weight: bold; */
        }
        .ace-monokai .ace_storage {
            color: #66D9EF !important;
        }
        .ace-monokai .ace_support.ace_function {
            color: #66ffef;
        }
        .ace_php_tag{            
            color: #F92672 !important;
        }
        .ace-monokai .ace_variable {
            color: #ffffff;
        }
        .ace-monokai .ace_identifier {
            color: #A6E22E;
        }
        .text-sangat-white{
            color:#ffffff
        }
        .nav-tabs .nav-item.show .nav-link, .nav-tabs .nav-link.active {
            background-color:#2e2e2e;
            color:#ffffff;
            border-color: #1e1f1c #1e1f1c #1e1f1c !important;
        }
        .bg-dark-monokai{
            background-color:#1e1f1c;
        }
        .ace-monokai{
            background-color:#272822 !important;
            /* background-color:#27282236 !important; */
        }
        .monokai-inactive-tab{
            background-color:#34352f !important;
        }
        .monokai-active-tab{
            background-color:#272822 !important;
            color:white !important;
        }
        .nav-tabs {
            border-bottom: 1px #1e1f1c !important;
        }
        .splitter-paneR{
            z-index:99;   
        }
    </style>
    <script src="//unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue-icons.min.js"></script>
    @verbatim
    <script type="text/x-template" id="item-template">
      <li>
        <div
          :class="{bold: isFolder}"
          @dblclick="addFile">
            <span class='no-select' style="cursor:pointer;" @click="toggle"><span v-if="isFolder&&2==1">[{{ isOpen ? '-' : '+' }}]</span>&nbsp;
                <span :style="isFolder?'text-decoration:underline;':''" 
                    onMouseOut="this.style.backgroundColor='#1e1f1c'"
                    onMouseOver="this.style.backgroundColor='#7388e6'" 
                    class='no-select'>
                    <b-icon :icon="item.icon" style="margin-right:5px;"></b-icon><span :style="item.migrated?'':'text-decoration: line-through red;'">{{ item.name }}</span> 
                    <b-icon size="sm" icon="pencil-square" style="margin-left:5px;" v-if="$store.state.activeEditorTitle==(item.name+'-'+item.src)"></b-icon>
                    <b-spinner variant="danger" type="grow" small label="Active" style="max-width:10px;max-height:10px;" v-if="!isChrome&&$store.state.activeEditorTitle==(item.name+'-'+item.src)"></b-spinner>
                    <span style="width:10px;height:10px;background-color: #dc3545;border-radius: 50%;display: inline-block;" v-if="isChrome&&$store.state.activeEditorTitle==(item.name+'-'+item.src)"></span>
                </span>
            </span>
        </div>
        <ul v-show="isOpen||(!toggled && $store.state.activeEditors.find(dt=>dt.title==item.src))" v-if="isFolder" style="list-style: none;padding-left:15px;">
          <tree-item
            style="cursor:pointer;"
            class="item"
            v-for="(child, index) in item.children"
            :key="index"
            :item="child"
            @open-file="$emit('open-file', $event)"
            @make-folder="$emit('make-folder', $event)"
            @add-item="$emit('add-item', $event)"
          ></tree-item>
          <!-- <li class="add" @click="$emit('add-item', item)">+</li> -->
        </ul>
      </li>
    </script>
    
</head>
<body class="bg-dark-monokai" style="overflow-y:hidden;scrollbar-width: none;">
<div id="app" >
    <split-pane @resize="resize" :min-percent='0' :default-percent="$store.state.sidebarLeft" split="vertical">
      <template ref="leftPanel" slot="paneL">
        <div style="z-index:1;min-width:250px;" class="text-white col-md-12 col-sm-12 col-xs-12">
                <b-row class="mt-md-2">
                    <b-col md="6" class="ml-md-2 mr-md-0" style="padding-right:1px;">
                        <b-form-input size="sm" autocomplete="off" placeholder='search' v-model.lazy="searchDataTemp" @keyup="searchCheck" @change="search" style="background-color: #34352f !important;color:white !important;"></b-form-input>
                    </b-col>
                    <b-col style="padding-left:0px;padding-right:0px;margin-right:0px;flex-grow: 0 !important;">
                        <b-btn title="add new" class="bg-dark-monokai" size="sm" style="margin-top:2px;margin-right:0px;" @click="add_new">
                            <b-icon icon="plus-square"></b-icon>
                        </b-btn>
                    </b-col>
                    <b-col style="padding-left:0px;">
                        <b-btn title="reload models" class="bg-dark-monokai" size="sm" style="margin-top:2px;margin-right:0px;" @click="reload_models">
                            <b-icon icon="arrow-clockwise"></b-icon>
                        </b-btn>
                    </b-col>
                </b-row>
                <div style="overflow: auto;height:100%;">
                <div class='ml-5' v-if="treeData.length==0">
                    <b-spinner style="width: 2rem; height: 2rem;margin-top:5%" label="Large Spinner"></b-spinner>
                </div>
                    <ul id="demo" style="list-style-type: none;margin: 3px;padding: 0;font-size:12px;padding-bottom:65px;">
                        <tree-item
                            v-for="(tree,index) in treeData"
                            class="item"
                            :item="tree"
                            @make-folder="addFile"
                            @add-item="addItem"
                        ></tree-item>
                        
                <!-- this.$emit("open-file", this.item); -->
                    </ul>
                </div>
        </div>
      </template>
      <template slot="paneR">
        <div>
            <b-tabs active-nav-item-class="font-weight-bold monokai-active-tab" no-fade small
                    content-class="mt-0" style="width:100%;" nav-class='monokai-inactive-tab' @input="changeTab" @changed="changedArrayTab">
                <b-tab  v-for="(item,index) in $store.state.activeEditors" :ref="item.jenis+'-'+item.title">
                    <template #title style="font-size:9px;">
                        <!-- <span class="dot" style="height: 9px;width: 9px;background-color: #dc3545;border-radius: 50%;display: inline-block;"
                        v-if="$store.state.activeEditorTitle==item.jenis+'-'+item.title"
                        ></span> -->
                        <b-spinner 
                            style="max-height:10px;max-width:10px;background-color: #dc3545;"
                            type="grow" 
                            small 
                            label="Active" 
                            v-if="$store.state.activeEditorTitle==item.jenis+'-'+item.title">
                        </b-spinner>
                        <b-icon size="sm" :icon="item.icon" style="max-height:15px;"></b-icon>
                        <small style="font-size:12px;color:#ccccc7 !important;" :title="item.jenis">{{item.title}} 
                            <!-- <span style='font-size:10px;'>{{item.jenis}}</span> -->
                        </small>
                        <span title="Close" class="monokai-inactive-tab" 
                            @click="$store.commit('removeActiveEditors',{ index:index, item:item})"
                            style="margin-left:5px;padding:3px !important;font-size:12px;border-radius:3px;"/>
                            &nbsp;x&nbsp;
                        </span>
                    </template>
                    <div style="max-height:94%">
                            <vue-ace-editor 
                                v-model.lazy:value="item.value"
                                v-bind:options="item" 
                                :id="'editor_'+index">
                            </vue-ace-editor>
                    </div>
                    <b-btn pill 
                        :disabled="$store.state.migrating"
                        @click="$store.dispatch(item.action, item)"
                        :class="item.action=='alter'?'bg-warning':(!item.migrated?'bg-success':'bg-danger')" 
                        style="z-index:999999;position:fixed;right:18px;bottom:30px;" 
                        v-if="item.action && item.action!='log' && item.action!='test'" :title="item.action" size="sm">
                        <b-icon :icon="!item.migrated?'arrow-up-circle':'lightning-fill'" v-if="!$store.state.migrating"></b-icon>
                        <b-spinner small type="grow" v-if="$store.state.migrating"></b-spinner><span v-if="$store.state.migrating">Altering/Migrating...</span>
                            
                    </b-btn>
                    <b-btn pill
                        v-if="item.action=='migrate' && item.migrated"
                        :disabled="$store.state.migrating"
                        @click="$store.dispatch('down', item)"
                        class="bg-default" 
                        style="z-index:999999;position:fixed;right:55px;bottom:30px;" 
                        title="Down" size="sm">
                        <b-icon icon="arrow-down-circle" v-if="!$store.state.migrating"></b-icon>                            
                    </b-btn>
                    <b-btn pill
                        v-if="item.action=='test' && item.migrated"
                        :disabled="$store.state.testing"
                        @click="$store.dispatch('test', item)"
                        class="bg-primary" 
                        style="z-index:999999;position:fixed;right:20px;bottom:30px;" 
                        title="Run Test" size="sm">
                        <b-icon icon="lightning-fill" v-if="!$store.state.testing"></b-icon>
                        <b-spinner small type="grow" v-if="$store.state.testing"></b-spinner><span v-if="$store.state.testing">Running test...</span>                        
                    </b-btn>
                    <b-btn pill
                        v-if="['log',''].includes(item.action)"
                        @click="$store.dispatch('reload', item)"
                        class="bg-default" 
                        style="z-index:999999;position:fixed;right:5px;bottom:30px;" 
                        title="Reload" size="sm">
                        <b-icon icon="arrow-clockwise" v-if="!$store.state.migrating"></b-icon>                            
                    </b-btn>
                </b-tab>
            </b-tabs>
        </div>
      </template>
    </split-pane>

        <b-modal v-model="$store.state.showModal" size="md" hide-footer title="Results" content-class="bg-dark text-white">
       
                <div class="text-left">
                    <p v-html="$store.state.modalHtml" />
                </div>

        </b-modal>
    <!-- <b-overlay :show="$store.state.prompt" no-wrap style="z-index: 999999">
        <template #overlay>          
          <div
            tabindex="-1"
            role="dialog"
            aria-modal="false"
          >
            <p><strong :class="$store.state.prompt_type">{{$store.state.prompt_text}}</strong></p>
            <div class="d-flex">
              <b-button variant="outline-success" @click="$store.state.prompt=!$store.state.prompt">OK</b-button>
            </div>
          </div>
        </template>
      </b-overlay> -->
</div>
@endverbatim
<script>

Vue.use(VueLoading);
Vue.component('loading', VueLoading)
Vue.component("tree-item", {
    template: "#item-template",
    props: {
        item: Object,
    },
    data: function() {
        return {
            isOpen: false,
            toggled:false,
            isChrome:false
        };
    },
    computed: {
        isFolder: function() {
            return this.item.children && this.item.children.length;
        }
    },
    created(){        
        this.isChrome = window.chrome!==undefined;
    },
    methods: {
        toggle: function() {
            if (this.isFolder) {
                this.isOpen = !this.isOpen;
                this.toggled=true;
            }else{
                this.$emit("open-file", this.item);
            }
        },
        addFile: function() {
            if (!this.isFolder) {
                this.$emit("make-folder", this.item);
                this.isOpen = true;
            }
        }
    }
});
const VueAceEditor = {
    props:['value','id','options'],
    template:`
        <textarea :id="id ? id: $options._componentTag +'-'+ _uid" 
             :class="$options._componentTag">
            <slot></slot>
        </textarea>
    `,
    watch:{
        value() {
            this.$emit('input', this.value);
            if(this.oldValue !== this.value){ 
                this.editor.setValue(this.value, 1); 
            }
        }
    },
    mounted(){
        //  editor
        this.editor = window.ace.edit(this.$el.id);
        let me = this;

        //  deprecation fix
        this.editor.$blockScrolling = Infinity;
        const session = this.editor.getSession();
        session.on("changeAnnotation", () => {
            const a = session.getAnnotations();
            const b = a.slice(0).filter( (item) => item.text.indexOf('DOC') == -1 );
            if(a.length > b.length) session.setAnnotations(b);
        });

        //  https://github.com/ajaxorg/ace/wiki/Configuring-Ace
        this.options = this.options || {};
        this.options.maxLines = this.options.maxLines || Infinity;
        this.options.printMargin = this.options.printMargin || false;      
        this.options.highlightActiveLine = this.options.highlightActiveLine || false;

        if(this.options.cursor === 'none' || this.options.cursor === false){
            this.editor.renderer.$cursorLayer.element.style.display = 'none';
            delete this.options.cursor; 
        }

        // if(this.options.mode && this.options.mode.indexOf('ace/mode/')===-1) {
            this.options.mode = `ace/mode/${this.options.mode}`;
        // }
        // if(this.options.theme && this.options.theme.indexOf('ace/theme/')===-1) {
            this.options.theme = `ace/theme/${this.options.theme}`;
        // }
        this.editor.setOptions(this.options);        
        if(!this.value || this.value === ''){
            this.$emit('input', this.editor.getValue());
        } else {
            this.editor.setValue(this.value, -1);
        }
        this.editor.on('change', () => {
             this.value = this.oldValue = this.editor.getValue();
        });
        me.editor.commands.addCommands([{
                name: "fullScreen2",
                exec: function(editor) {
                    if(!me.$store.state.sidebar){
                        document.getElementsByClassName('splitter-paneR')[0].style.width="99%";
                        document.getElementsByClassName('splitter-pane-resizer')[0].style.left="1%";
                    }else{
                        document.getElementsByClassName('splitter-paneR')[0].style.width=(100-me.$store.state.sidebarLeft)+"%";
                        document.getElementsByClassName('splitter-pane-resizer')[0].style.left=(me.$store.state.sidebarLeft)+"%";
                    }
                    me.$store.commit('sidebarChange',!me.$store.state.sidebar);
                },
                readOnly: true
            },{
                name: "toggleWordWrap",
                exec: function(editor) {
                    var wrapUsed = editor.session.getUseWrapMode();
                    editor.session.setUseWrapMode(!wrapUsed);
                },
                readOnly: true
            }, {
                name: "navigateToLastEditLocation",
                exec: function(editor) {
                    var lastDelta = editor.session.getUndoManager().$lastDelta;
                    var range = (lastDelta.action  == "remove")? lastDelta.start: lastDelta.end;
                    editor.moveCursorTo(range.row, range.column);
                    editor.clearSelection();
                }
            }, {
                name: "replaceAll",
                exec: function (editor) {
                    if (!editor.searchBox) {
                        config.loadModule("ace/ext/searchbox", function(e) {
                            e.Search(editor, true);
                        });
                    } else {
                        if (editor.searchBox.active === true && editor.searchBox.replaceOption.checked === true) {
                            editor.searchBox.replaceAll();
                        }
                    }
                }
            }, {
                name: "replaceOne",
                exec: function (editor) {
                    if (!editor.searchBox) {
                        config.loadModule("ace/ext/searchbox", function(e) {
                            e.Search(editor, true);
                        });
                    } else {
                        if (editor.searchBox.active === true && editor.searchBox.replaceOption.checked === true) {
                            editor.searchBox.replace();
                        }
                    }
                }
            }, {
                name: "search",
                exec: function (editor) {
                    if (!editor.searchBox) {
                        config.loadModule("ace/ext/searchbox", function(e) {
                            e.Search(editor, false);
                        });
                    } else {
                        if (editor.searchBox.active === true) {
                            editor.searchBox.findAll();
                        }
                    }
                }
            }, {
                name: "toggleFindCaseSensitive",
                exec: function (editor) {
                    config.loadModule("ace/ext/searchbox", function(e) {
                        e.Search(editor, false);
                        var sb = editor.searchBox;
                        sb.caseSensitiveOption.checked = !sb.caseSensitiveOption.checked;
                        sb.$syncOptions();
                    });

                }
            }, {
                name: "toggleFindInSelection",
                exec: function (editor) {
                    config.loadModule("ace/ext/searchbox", function(e) {
                        e.Search(editor, false);
                        var sb = editor.searchBox;
                        sb.searchOption.checked = !sb.searchRange;
                        sb.setSearchRange(sb.searchOption.checked && sb.editor.getSelectionRange());
                        sb.$syncOptions();
                    });
                }
            }, {
                name: "toggleFindRegex",
                exec: function (editor) {
                    config.loadModule("ace/ext/searchbox", function(e) {
                        e.Search(editor, false);
                        var sb = editor.searchBox;
                        sb.regExpOption.checked = !sb.regExpOption.checked;
                        sb.$syncOptions();
                    });
                }
            }, {
                name: "toggleFindWholeWord",
                exec: function (editor) {
                    config.loadModule("ace/ext/searchbox", function(e) {
                        e.Search(editor, false);
                        var sb = editor.searchBox;
                        sb.wholeWordOption.checked = !sb.wholeWordOption.checked;
                        sb.$syncOptions();
                    });
                }
            }, {
                name: "removeSecondaryCursors",
                exec: function (editor) {
                    var ranges = editor.selection.ranges;
                    if (ranges && ranges.length > 1)
                        editor.selection.toSingleRange(ranges[ranges.length - 1]);
                    else
                        editor.selection.clearSelection();
                }
            }, {
                    name: "saveOnline",
                    exec: function (editor) {
                        me.$store.dispatch("saveOnline",editor)
                    }
            }]);
                [{
                    bindKey: {mac: "Ctrl-S", win: "Ctrl-S"},
                    name: "saveOnline"
                },{
                    bindKey: {mac: "Ctrl-Enter", win: "Ctrl-Enter"},
                    name: "fullScreen2"
                }, {
                    bindKey: {mac: "Ctrl-G", win: "Ctrl-G"},
                    name: "gotoline"
                }, {
                    bindKey: {mac: "Command-Shift-L|Command-F2", win: "Ctrl-Shift-L|Ctrl-F2"},
                    name: "findAll"
                }, {
                    bindKey: {mac: "Shift-F8|Shift-Option-F8", win: "Shift-F8|Shift-Alt-F8"},
                    name: "goToPreviousError"
                }, {
                    bindKey: {mac: "F8|Option-F8", win: "F8|Alt-F8"},
                    name: "goToNextError"
                }, {
                    bindKey: {mac: "Command-Shift-P|F1", win: "Ctrl-Shift-P|F1"},
                    name: "openCommandPallete"
                }, {
                    bindKey: {mac: "Command-K|Command-S", win: "Ctrl-K|Ctrl-S"},
                    name: "showKeyboardShortcuts"
                }, {
                    bindKey: {mac: "Shift-Option-Up", win: "Alt-Shift-Up"},
                    name: "copylinesup"
                }, {
                    bindKey: {mac: "Shift-Option-Down", win: "Alt-Shift-Down"},
                    name: "copylinesdown"
                }, {
                    bindKey: {mac: "Command-Shift-K", win: "Ctrl-Shift-K"},
                    name: "removeline"
                }, {
                    bindKey: {mac: "Command-Enter", win: "Ctrl-Enter"},
                    name: "addLineAfter"
                }, {
                    bindKey: {mac: "Command-Shift-Enter", win: "Ctrl-Shift-Enter"},
                    name: "addLineBefore"
                }, {
                    bindKey: {mac: "Command-Shift-\\", win: "Ctrl-Shift-\\"},
                    name: "jumptomatching"
                }, {
                    bindKey: {mac: "Command-]", win: "Ctrl-]"},
                    name: "blockindent"
                }, {
                    bindKey: {mac: "Command-[", win: "Ctrl-["},
                    name: "blockoutdent"
                }, {
                    bindKey: {mac: "Ctrl-PageDown", win: "Alt-PageDown"},
                    name: "pagedown"
                }, {
                    bindKey: {mac: "Ctrl-PageUp", win: "Alt-PageUp"},
                    name: "pageup"
                }, {
                    bindKey: {mac: "Shift-Option-A", win: "Shift-Alt-A"},
                    name: "toggleBlockComment"
                }, {
                    bindKey: {mac: "Option-Z", win: "Alt-Z"},
                    name: "toggleWordWrap"
                }, {
                    bindKey: {mac: "Command-G", win: "F3|Ctrl-K Ctrl-D"},
                    name: "findnext"
                }, {
                    bindKey: {mac: "Command-Shift-G", win: "Shift-F3"},
                    name: "findprevious"
                }, {
                    bindKey: {mac: "Option-Enter", win: "Alt-Enter|Ctrl-B"},
                    name: "fullScreen2"
                }, {
                    bindKey: {mac: "Command-D", win: "Ctrl-D"},
                    name: "selectMoreAfter"
                }, {
                    bindKey: {mac: "Command-K Command-D", win: "Ctrl-K Ctrl-D"},
                    name: "selectOrFindNext"
                }, {
                    bindKey: {mac: "Shift-Option-I", win: "Shift-Alt-I"},
                    name: "splitSelectionIntoLines"
                }, {
                    bindKey: {mac: "Command-K M", win: "Ctrl-K M"},
                    name: "modeSelect"
                }, {
                    // In VsCode this command is used only for folding instead of toggling fold
                    bindKey: {mac: "Command-Option-[", win: "Ctrl-Shift-["},
                    name: "toggleFoldWidget"
                }, {
                    bindKey: {mac: "Command-Option-]", win: "Ctrl-Shift-]"},
                    name: "toggleFoldWidget"
                }, {
                    bindKey: {mac: "Command-K Command-0", win: "Ctrl-K Ctrl-0"},
                    name: "foldall"
                }, {
                    bindKey: {mac: "Command-K Command-J", win: "Ctrl-K Ctrl-J"},
                    name: "unfoldall"
                }, {
                    bindKey: { mac: "Command-K Command-1", win: "Ctrl-K Ctrl-1" },
                    name: "foldOther"
                }, {
                    bindKey: { mac: "Command-K Command-Q", win: "Ctrl-K Ctrl-Q" },
                    name: "navigateToLastEditLocation"
                }, {
                    bindKey: { mac: "Command-K Command-R|Command-K Command-S", win: "Ctrl-K Ctrl-R|Ctrl-K Ctrl-S" },
                    name: "showKeyboardShortcuts"
                }, {
                    bindKey: { mac: "Command-K Command-X", win: "Ctrl-K Ctrl-X" },
                    name: "trimTrailingSpace"
                }, {
                    bindKey: {mac: "Shift-Down|Command-Shift-Down", win: "Shift-Down|Ctrl-Shift-Down"},
                    name: "selectdown"
                }, {
                    bindKey: {mac: "Shift-Up|Command-Shift-Up", win: "Shift-Up|Ctrl-Shift-Up"},
                    name: "selectup"
                }, {
                    // TODO: add similar command to work inside SearchBox
                    bindKey: {mac: "Command-Alt-Enter", win: "Ctrl-Alt-Enter"},
                    name: "replaceAll"
                }, {
                    // TODO: add similar command to work inside SearchBox
                    bindKey: {mac: "Command-Shift-1", win: "Ctrl-Shift-1"},
                    name: "replaceOne"
                }, {
                    bindKey: {mac: "Option-C", win: "Alt-C"},
                    name: "toggleFindCaseSensitive"
                }, {
                    bindKey: {mac: "Option-L", win: "Alt-L"},
                    name: "toggleFindInSelection"
                }, {
                    bindKey: {mac: "Option-R", win: "Alt-R"},
                    name: "toggleFindRegex"
                }, {
                    bindKey: {mac: "Option-W", win: "Alt-W"},
                    name: "toggleFindWholeWord"
                }, {
                    bindKey: {mac: "Command-L", win: "Ctrl-L"},
                    name: "expandtoline"
                }, {
                    bindKey: {mac: "Shift-Esc", win: "Shift-Esc"},
                    name: "removeSecondaryCursors"
                } 
                // not implemented
                /*{
                    bindKey: {mac: "Option-Shift-Command-Right", win: "Shift-Alt-Right"},
                    name: "smartSelect.expand"
                }, {
                    bindKey: {mac: "Ctrl-Shift-Command-Left", win: "Shift-Alt-Left"},
                    name: "smartSelect.shrink"
                }, {
                    bindKey: {mac: "Shift-Option-F", win: "Shift-Alt-F"},
                    name: "beautify"
                }, {
                    bindKey: {mac: "Command-K Command-F", win: "Ctrl-K Ctrl-F"},
                    name: "formatSelection"
                }, {
                    bindKey: {mac: "Command-K Command-C", win: "Ctrl-K Ctrl-C"},
                    name: "addCommentLine"
                }, {
                    bindKey: {mac: "Command-K Command-U", win: "Ctrl-K Ctrl-U"},
                    name: "removeCommentLine"
                }, {
                    bindKey: {mac: "Command-K Command-/", win: "Ctrl-K Ctrl-/"},
                    name: "foldAllBlockComments"
                }, {
                    bindKey: {mac: "Command-K Command-2", win: "Ctrl-K Ctrl-2"},
                    name: "foldLevel2"
                }, {
                    bindKey: {mac: "Command-K Command-3", win: "Ctrl-K Ctrl-3"},
                    name: "foldLevel3"
                }, {
                    bindKey: {mac: "Command-K Command-4", win: "Ctrl-K Ctrl-4"},
                    name: "foldLevel4"
                }, {
                    bindKey: {mac: "Command-K Command-5", win: "Ctrl-K Ctrl-5"},
                    name: "foldLevel5"
                }, {
                    bindKey: {mac: "Command-K Command-6", win: "Ctrl-K Ctrl-6"},
                    name: "foldLevel6"
                }, {
                    bindKey: {mac: "Command-K Command-7", win: "Ctrl-K Ctrl-7"},
                    name: "foldLevel7"
                }, {
                    bindKey: {mac: "Command-K Command-[", win: "Ctrl-K Ctrl-["},
                    name: "foldRecursively"
                }, {
                    bindKey: {mac: "Command-K Command-8", win: "Ctrl-K Ctrl-8"},
                    name: "foldAllMarkerRegions"
                }, {
                    bindKey: {mac: "Command-K Command-9", win: "Ctrl-K Ctrl-9"},
                    name: "unfoldAllMarkerRegions"
                }, {
                    bindKey: {mac: "Command-K Command-]", win: "Ctrl-K Ctrl-]"},
                    name: "unfoldRecursively"
                }, {
                    bindKey: {mac: "Command-K Command-T", win: "Ctrl-K Ctrl-T"},
                    name: "selectTheme"
                }, {
                    bindKey: {mac: "Command-K Command-M", win: "Ctrl-K Ctrl-M"},
                    name: "selectKeymap"
                }, {
                    bindKey: {mac: "Command-U", win: "Ctrl-U"},
                    name: "cursorUndo"
                }*/
        ].forEach(function(binding) {
            var command = me.editor.commands.byName[binding.name];
            if (command){
                me.editor.commands.byName[binding.name].bindKey = binding.bindKey;
            }
            me.editor.commands.bindKey( binding.bindKey,binding.name)
        });

    },
    methods:{ 
        editor(){ return this.editor } }
};


Vue.component('split-pane', SplitPane.SplitPane);
// Vue.component('v-select', VueSelect.VueSelect);
var mixin = {
  data: function () {
    return {     
    }
  },
  methods:{
      saveOnlineMixin(data){

      }
  }
}
const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 2000,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.addEventListener('mouseenter', Swal.stopTimer)
    toast.addEventListener('mouseleave', Swal.resumeTimer)
  }
})
vm = new Vue({
    el: '#app',
    mixins: [mixin],
    store: new Vuex.Store(
        {
            state: {
                sidebar: false,
                sidebarLeft:25,
                activeEditorIndex:0,
                activeEditorTitle:"-",
                activeEditors:[],
                modelList:[],
                prompt:false,
                prompt_type:"text-danger",
                prompt_text:"errors",   
                migrating:false,
                testing:false,
                showModal:false,
                modalHtml:``
            },
            mutations: {
                changeTab(state,index){
                    // if(state.activeEditorIndex==index){
                    //     return;
                    // }
                    try{
                        // state.activeEditorIndex=index;
                        let item = state.activeEditors[index];
                        // console.log(item.jenis+'-'+item.title)
                        state.activeEditorTitle=item.jenis+'-'+item.title;
                    }catch(e){}
                },
                sidebarChange (state,val) {
                    state.sidebar=val;
                },
                sidebarLeftChange (state,val) {
                    state.sidebarLeft=val;
                },
                addActiveEditors(state,objVal){
                    // console.log(objVal)
                    if(objVal['value']===undefined){
                        let ketemu = state.activeEditors.findIndex(dt=>{ return (dt.title==objVal.title&&dt.jenis==objVal.jenis);} );
                        // state.activeEditors[ketemu].value=objVal.value;
                        // state.activeEditorIndex = ketemu;
                        state.activeEditorTitle = state.activeEditors[ketemu].jenis+'-'+state.activeEditors[ketemu].title;
                        return;
                    }

                    state.activeEditors.push(objVal);
                    setTimeout(function() {                        
                        state.activeEditorTitle = objVal.jenis+'-'+objVal.title;
                        vm.$refs[ state.activeEditorTitle][0].activate()
                    }, 300);
                },
                updateActiveEditors(state,objVal){
                    let ketemu = state.activeEditors.findIndex(dt=>{ return (dt.title==objVal.title&&dt.jenis==objVal.jenis);} );
                    if(ketemu>-1){
                        Object.assign(state.activeEditors[ketemu],objVal);
                        return;
                    }
                },
                removeActiveEditors(state,dt){
                    let confirm = window.confirm(`Close [${dt.item.jenis}] ${dt.item.title}?`);
                    if(confirm){
                        try{
                            state.activeEditors = state.activeEditors.filter((data,i)=>{ return i!=dt.index;});
                            state.activeEditorTitle = state.activeEditors[0].jenis+'-'+state.activeEditors[0].title;
                        }catch(e){}
                        }
                },
                removeActiveEditorsAll(state,title){
                    try{
                        state.activeEditors = state.activeEditors.filter((data)=>{ return data.title!=title;});
                        state.activeEditorTitle = state.activeEditors[0].jenis+'-'+state.activeEditors[0].title;
                    }catch(e){}
                }
            },
            actions: {
                saveOnline ({ commit, state }, editor) {
                    let activeEditor = state.activeEditors.find(dt=>{
                        return state.activeEditorTitle == dt.jenis+'-'+dt.title;
                    });
                    if((activeEditor.jenis).toLowerCase().includes("basic")){
                        Toast.fire({
                            icon: 'warning',
                            title: 'Never Saved basic model!'
                        })
                        return false;
                    }
                    let errors = editor.getSession().getAnnotations();
                    for(let i in errors){
                        if(errors[i].type=='error'){
                            Swal.fire({
                                title: `Line ${errors[i].row+1}!`,
                                text: errors[i].text,
                                icon: 'error',
                                confirmButtonText: 'Ok!'
                            })
                            return;
                        }
                    }
                    
                    let operation = "model";
                    if((activeEditor.jenis).toLowerCase().includes("alter")){
                        operation = "alter";
                    }else if((activeEditor.jenis).toLowerCase().includes("model")){
                        operation = "models";
                    }else if((activeEditor.jenis).toLowerCase().includes("migration")){
                        operation = "migrations";
                    }else if((activeEditor.jenis).toLowerCase().includes("testing")){
                        operation = "tests";
                    }
                    
                    
                    axios({
                        url         : `{{url('laradev')}}/${operation}/${activeEditor.title}`,
                        method      : 'put',
                        credentials : true,
                        data        : {
                            text:editor.getSession().getValue()
                        },
                        headers     : {
                            laradev:"{{env('LARADEVPASSWORD','bismillah')}}"
                        }
                    }).then(response => {
                        Toast.fire({
                            icon: 'success',
                            title: 'Saved Successfully'
                        })
                    }).catch(error => {
                        window.console.clear();
                        Swal.fire({
                            title: `Failed!`,
                            text: error.response.data,
                            icon: 'error',
                            confirmButtonText: 'Ok!'
                        })
                        console.log(error.response.data)
                    }).then(function () {
                    });
                    
                },
                async alter({commit,state}, item){
                    const { value: confirm } = await Swal.fire({
                        title: 'Penting',
                        input: 'checkbox',
                        inputValue: 0,
                        inputPlaceholder:
                            `Saya bertanggung jawab atas ${item.title}!`,
                        confirmButtonText:
                            'ALTER &nbsp;<i class="fa fa-check"></i>',
                        inputValidator: (result) => {
                            return !result && 'OK anda belum yakin'
                        }
                    });
                    if(confirm){
                        state.migrating = true;
                        axios({
                            url         : `{{url('laradev/migrate')}}/${item.title}?alter=true`,
                            method      : 'get',
                            credentials : true,
                            body        : null,
                            headers     : {
                                laradev:"{{env('LARADEVPASSWORD','bismillah')}}"
                            }
                        }).then(response => {
                            Toast.fire({
                                icon: 'info',
                                title: `${item.title} has been altered Successfully`
                            })
                        }).catch(error => {
                            window.console.clear();
                            Swal.fire({
                                title: `Failed!`,
                                text: 'Check Your Console',
                                icon: 'error',
                                confirmButtonText: 'Ok!'
                            })
                            console.log(error.response.data)
                        }).then(function () {
                            state.migrating = false;
                        });
                    }
                },
                async migrate({commit,state}, item){
                    const { value: confirm } = await Swal.fire({
                        title: 'Penting',
                        input: 'checkbox',
                        inputValue: 0,
                        inputPlaceholder:
                            `Saya bertanggung jawab atas ${item.title}!`,
                        confirmButtonText:
                            'Force Migrate&nbsp;<i class="fa fa-check"></i>',
                        inputValidator: (result) => {
                            return !result && 'OK anda belum yakin'
                        }
                    });
                    if(confirm){
                        state.migrating = true;
                        axios({
                            url         : `{{url('laradev/migrate')}}/${item.title}`,
                            method      : 'get',
                            credentials : true,
                            body        : null,
                            headers     : {
                                laradev:"{{env('LARADEVPASSWORD','bismillah')}}"
                            }
                        }).then(response => {
                            Toast.fire({
                                icon: 'info',
                                title: `${item.title} has been migrated Successfully`
                            })
                            commit('updateActiveEditors',{
                                title:item.title,
                                jenis:item.jenis,
                                migrated:true
                            });
                            Object.assign(state.modelList.models.find(dt=>dt.file == item.title+".php"),{
                                table   : true, model:true
                            });
                        }).catch(error => {
                            window.console.clear();
                            Swal.fire({
                                title: `Failed!`,
                                text: 'Check Your Console',
                                icon: 'error',
                                confirmButtonText: 'Ok!'
                            })
                            console.log(error.response.data)
                        }).then(function () {
                            state.migrating = false;
                        });
                    }
                },
                async down({commit,state}, item){
                    let me = this;
                    const { value: confirm } = await Swal.fire({
                        title: 'Penting',
                        input: 'checkbox',
                        inputValue: 0,
                        inputPlaceholder:
                            `Saya bertanggung jawab atas ${item.title}!`,
                        confirmButtonText:
                            'Force Drop&nbsp;<i class="fa fa-check"></i>',
                        inputValidator: (result) => {
                            return !result && 'OK anda belum yakin'
                        }
                    });
                    if(confirm){
                        state.migrating = true;
                        axios({
                            url         : `{{url('laradev/migrate')}}/${item.title}?down=true`,
                            method      : 'get',
                            credentials : true,
                            body        : null,
                            headers     : {
                                laradev:"{{env('LARADEVPASSWORD','bismillah')}}"
                            }
                        }).then(response => {
                            Toast.fire({
                                icon: 'info',
                                title: `${item.title} has been dropped Successfully`
                            });
                            commit('updateActiveEditors',{
                                title:item.title,
                                jenis:item.jenis,
                                migrated:false
                            });
                            state.modelList.models.find(dt=>dt.file == item.title+".php").table=false;
                        }).catch(error => {
                            window.console.clear();
                            Swal.fire({
                                title: `Failed!`,
                                text: 'Check Your Console',
                                icon: 'error',
                                confirmButtonText: 'Ok!'
                            })
                            console.log(error.response.data)
                        }).then(function () {
                            state.migrating = false;
                        });
                    }
                },
                async test({commit,state}, item){
                    let me = this;
                    state.testing = true;
                    axios({
                        url         : `{{url('laradev/do-test')}}/${item.title}`,
                        method      : 'get',
                        credentials : true,
                        body        : null,
                        headers     : {
                            laradev:"{{env('LARADEVPASSWORD','bismillah')}}"
                        }
                    }).then(response => {
                        state.modalHtml=response.data.text+'<br/>'+response.data.output
                        state.showModal=true

                    }).catch(error => {
                        window.console.clear();
                        Swal.fire({
                            title: `Failed!`,
                            text: 'Check Your Console',
                            icon: 'error',
                            confirmButtonText: 'Ok!'
                        })
                        console.log(error.response.data)
                    }).then(function () {
                        state.testing = false;
                    });
                    
                },
                async reload({commit,state}, item){
                    const { value: confirm } = await Swal.fire({
                        title: `Reload Model?`,
                        text: "Semua Perubahan diReload Ulang dari Server",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Reload!'
                    });
                    if(!confirm){ return; }
                    let url = "{{url('laradev')}}";
                    if(item.jenis=='Log'){
                        url=url+"/logs/"+item.title;
                    }else if( (item.jenis).toLowerCase().includes('custom')){
                        url=url+"/models/"+item.title+"?custom=true";
                    }else if((item.jenis).toLowerCase().includes('basic')){
                        url=url+"/models/"+item.title+"?basic=true&reload=true";
                    }
                    axios({
                        url         : url,
                        method      : 'GET',
                        credentials : true,
                        body        : null,
                        headers     : {
                            laradev:"{{env('LARADEVPASSWORD','bismillah')}}"
                        }
                    }).then(response => {
                        Toast.fire({
                            icon: 'info',
                            title: `${item.title} model has been reloaded sucessfully`
                        });
                        commit('updateActiveEditors',{
                            title:item.title,
                            jenis:item.jenis,
                            value:item.jenis=='Log'?JSON.stringify(response.data, null, 2):response.data
                        });
                    }).catch(error => {
                        window.console.clear();
                        Swal.fire({
                            title: `Failed!`,
                            text: 'Check Your Console',
                            icon: 'error',
                            confirmButtonText: 'Ok!'
                        })
                        console.log(error.response.data)
                    }).then(function () {
                        
                    });
                },
                getModels({commit,state}){
                    // let loader = Vue.$loading.show({
                    //     color: 'grey',loader: 'dots',
                    // },{
                        
                    // });
                    let oldList = state.modelList;
                    state.modelList=[];
                    axios({
                        url         : "{{url('laradev/models')}}",
                        credentials : true,
                        // method      : data.method,
                        // data        : data.body,
                        headers     : {
                            laradev:"{{env('LARADEVPASSWORD','bismillah')}}"
                        }
                    }).then(response => {
                        state.modelList = response.data;
                    }).catch(error => {
                        window.console.clear();
                        Swal.fire({
                            title: `Failed to Load Models!`,
                            text: 'Check Your Console',
                            icon: 'error',
                            confirmButtonText: 'Ok!'
                        })
                        console.log(error.response.data)
                        state.modelList = oldList;
                    }).then(function () {
                        // loader.hide();
                    });;
                }
            }
        }
    ),
    components:{ 'vue-ace-editor': VueAceEditor },
    data:{
        editorcontent: 'tes',
        searchData:"",
        searchDataTemp:"",
        connection:null,
        isChrome:false,
        channel:"{{env('LOG_CHANNEL',"+btoa(window.location.host)+")}}"
        //  https://github.com/ajaxorg/ace/wiki/Configuring-Ace
    },
    created(){
        this.isChrome = window.chrome!==undefined;
        this.$store.dispatch('getModels');
        this.connect();
    },
    computed:{
        treeData:function(){
            var treeData = [];
            let models=this.$store.state.modelList.models;
            // console.log(models)
            for(let i in models){
                if(this.searchData!="" && this.searchData!==null){
                    if(!( (models[i].file).toLowerCase() ).includes( (this.searchData).toLowerCase() )){
                        continue;
                    }
                }
                let name = 'migration';
                let icon = 'table';
                const filename = (models[i].file).split(".")[0];
                let children = [
                    { migrated:true,name: 'Migration', icon:"card-checklist",src:filename },
                    { migrated:true,name: "Alter",icon:"bookmark-plus",src:filename },
                    { migrated:true,name: "Basic Model",icon:"check2-square",src:filename },
                    { migrated:true,name: "Custom Model",icon:"code-square",src:filename },
                    { migrated:true,name: "Log", icon:"search", src:filename}
                ];
                if((models[i].file).includes("_after_") || (models[i].file).includes("_before_")){
                    name='trigger'; icon = 'lightning-fill';
                    children =[{ migrated:true,name: 'Migration', icon:"lightning-fill",src:filename }];
                }else if(models[i].view){
                    children.splice(1,1)
                    name='view'; icon = 'eye-fill';
                }else if(models[i].alias){
                    children.splice(0,2)
                    name='alias'; icon = 'stickies';
                }
                if(!models[i].model){
                    children = children.filter(dt=>{
                        return !dt.name.includes("Model") && !dt.name.includes("Alter")
                    })
                }
                children.push({
                    migrated:true,name: "Testing",icon:"hammer",src:filename
                })
                children.push({
                    migrated:true,name: "Last 10 Rows",icon:"eye",src:filename
                })
                children.push({
                    migrated:true,name: "Delete",icon:"trash",src:filename
                })
                treeData.push({
                    name: filename,
                    icon: icon,
                    children: children,
                    migrated:models[i].table||models[i].alias,
                    src:filename
                });
            }
            return treeData;
        }
    },
    methods: {
        connect(){
            let me = this;
            Toast.fire({
                icon: 'warning',
                title: `Debugger is trying to connect to channel [${me.channel}]...`
            });
            if( "{{env('LOG_PROTOCOLS')}}" ){
                const protocolStr = "{{env('LOG_PROTOCOLS')}}";
                this.connection = new WebSocket("{{env('LOG_SERVER')}}/"+me.channel, protocolStr.split(","));
            }else{
                this.connection = new WebSocket("{{env('LOG_SERVER')}}/"+me.channel);
            }

            this.connection.onopen = function() {
                console.log("%c debug is ready to use","background: #222; color: #a0ff5c;font-weight: bold;");
                Toast.fire({
                    icon: 'success',
                    title: `Debug is Ready`
                });
            };
            this.connection.onmessage = function (evt) { 
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
            this.connection.onclose = function() {
                console.log("connection is closed, trying to reconnect...");
                setTimeout(function() {
                    Toast.fire({
                        icon: 'error',
                        title: `Debug is Closed, reconnecting...`
                    });
                    me.connect();
                }, 2000);
            };
        },
        changeTab(index){
            this.$store.commit('changeTab',index);
        },
        changedArrayTab(now,prev){
            if(now.length>prev.length){
                now[now.length-1].activate()
            }
        },
        add_new(){
            let me = this;
            var modul = prompt("Nama Migration (standard : (3)modul_(3)submodul_processname):", "");
            if (modul == null || modul == "") {
            } else {
                axios({
                    url         : `{{url('laradev/migrations')}}`,
                    method      : 'post',
                    credentials : true,
                    data        : {
                        modul:modul
                    },
                    headers     : {
                        laradev:"{{env('LARADEVPASSWORD','bismillah')}}"
                    }
                }).then(response => {
                    Toast.fire({
                        icon: 'info',
                        title: `${modul} has been created Successfully`
                    });
                    me.$store.dispatch('getModels');
                }).catch(error => {
                    window.console.clear();
                    Swal.fire({
                        title: `Failed!`,
                        text: 'Check Your Console',
                        icon: 'error',
                        confirmButtonText: 'Ok!'
                    })
                    console.log(error.response.data)
                }).then(function () {
                });
            }
        },
        reload_models(){
            this.$store.dispatch('getModels');
        },
        search(e){
            this.searchData=e
        },
        searchCheck(e){
            if(e.key=='Escape'){
                this.searchData = null
                this.searchDataTemp = null
            }
        },
        resize(a){
            this.$store.commit('sidebarLeftChange',a);
        },
        addFile: function(item,e) {
            let itemLengkap = this.treeData.find(dt=>dt.name==item.src);
            let icon,action,endpoint,me=this;
            if(item.name=='Delete'){
                var password = prompt(`[${itemLengkap.name}] Migration, Model, Table akan hilang!, password:`, "");
                if (password == null || password == "") {
                    return;
                } else {
                    axios({
                        url         : "{{url('laradev/trio')}}/"+itemLengkap.name,
                        method      : 'post',
                        credentials : true,
                        data        : {
                            password: password
                        },
                        headers     : {
                            laradev:"{{env('LARADEVPASSWORD','bismillah')}}"
                        }
                    }).then(response => {
                        Toast.fire({
                            icon: 'info',
                            title: `${itemLengkap.name} has been deleted Successfully`
                        });
                        me.$store.dispatch('getModels');
                        me.$store.commit('removeActiveEditorsAll',itemLengkap.name);
                    }).catch(error => {
                        window.console.clear();
                        Swal.fire({
                            title: `Failed!`,
                            text: error.response.status==401?"Password Salah!":'Check Your Console',
                            icon: 'error',
                            confirmButtonText: 'Ok!'
                        })
                    }).then(function () {
                        return;
                    });
                }
                return;
            }
            if(item.name=='Migration'){
                icon='lightning'; action='migrate'; endpoint="/migrations/"+item.src;
            }else if(item.name=='Testing'){
                icon='hammer'; action='test';endpoint="/tests/"+item.src;
            }else if( item.name=='Last 10 Rows' ){
                axios({
                    url         : "{{url('laradev/queries10rows')}}/"+itemLengkap.name,
                    method      : 'get',
                    credentials : true,
                    headers     : {
                        laradev:"{{env('LARADEVPASSWORD','bismillah')}}"
                    }
                }).then(response => {
                    me.$store.state.modalHtml=response.data
                    me.$store.state.showModal=true
                }).catch(error => {
                    window.console.clear();
                    Swal.fire({
                        title: `Failed!`,
                        text: 'Check Your Console',
                        icon: 'error',
                        confirmButtonText: 'Ok!'
                    })
                }).then(function () {
                    return;
                });
                return
            }else if(item.name=='Log'){
                icon='search'; action='log';endpoint="/logs/"+item.src;
            }else if(item.name=='Alter'){
                icon='shuffle'; action='alter';endpoint="/alter/"+item.src;
            }else if( (item.name).toLowerCase().includes('custom')){
                icon='file-code';action="";endpoint="/models/"+item.src+"?custom=true";
            }else if((item.name).toLowerCase().includes('basic')){
                icon='file-check';action="";endpoint="/models/"+item.src+"?basic=true";
            }else{
                icon='list-check';action="";
            }
            let ketemu = this.$store.state.activeEditors.find(dt=>{ 
                return (dt.title==itemLengkap.name&&dt.jenis==item.name);
            } );
            if(ketemu){      
                me.$store.commit('addActiveEditors',{
                    title:itemLengkap.name,
                    jenis:item.name
                })
                me.$refs[ me.$store.state.activeEditorTitle][0].activate()
                return;
            }
            let loader = Vue.$loading.show({
                color: 'grey',loader: 'dots',
            },{
                
            });
            axios({
                url         : "{{url('laradev')}}"+endpoint,
                credentials : true,
                // method      : data.method,
                // data        : data.body,
                headers     : {
                    laradev:"{{env('LARADEVPASSWORD','bismillah')}}"
                }
            }).then(response => {
                me.$store.commit('addActiveEditors',{
                    title:itemLengkap.name,
                    jenis:item.name,
                    value:(item.name=='Log'?JSON.stringify(response.data, null, 2):response.data),
                    icon: icon,
                    readOnly:(item.name).toLowerCase().includes('basic')||item.name=='Log',
                    action:action,
                    migrated:itemLengkap.migrated,
                    mode:item.name=='Log'?'json':'php',
                    theme: (item.name).toLowerCase().includes('basic')||item.name=='Log'?'chrome':'monokai',
                    fontSize: 11,
                    fontFamily: item.name=='Log'?'Monospace':'Consolas',
                    highlightActiveLine: true,
                    enableBasicAutocompletion:true,
                    maxLines:parseInt(window.innerHeight/14.3),
                    minLines:parseInt(window.innerHeight/14.3)
                })
                setTimeout(function(){
                    try{
                        var theScroll = document.getElementsByClassName("nav-tabs")[0];
                        theScroll.scrollTo(theScroll.scrollLeftMax,0)
                    }catch(e){}
                },1000)
            }).catch(error => {
                try{
                    Swal.fire({
                        title: `File Error!`,
                        text: error.response.data.message,
                        icon: 'error',
                        confirmButtonText: 'Ok!'
                    })
                }catch(e){console.log(e)}
            }).then(function () {
                loader.hide();
            });
        },
        openFile: function(item) {
            //console.log(item)
            // Vue.set(item, "children", []);
            // this.addItem(item);
        },
        addItem: function(item) {
            return;
            // item.children.push({
            //     name: "new stuff"
            // });
        }
    }
});
// document.addEventListener('DOMContentLoaded', ()=>{
//     if(localStorage.scrollY!==undefined){
//         window.scrollTo({
//             top: localStorage.scrollY,
//             left: 0,
//             behavior: 'smooth'
//         });
//     }
// }, false);
const scrollContainer = document.getElementsByClassName("nav-tabs")[0];

scrollContainer.addEventListener("wheel", (evt) => {
    evt.preventDefault();
    scrollContainer.scrollLeft += evt.deltaY;
});
</script>
</body>
</html>