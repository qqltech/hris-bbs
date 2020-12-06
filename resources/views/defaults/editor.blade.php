<html>
<head>
    <title> EDITOR </title>
    <link rel="icon" href="{{url('favicon.ico')}}">
    <script src="//cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ext-language_tools.min.js" integrity="sha512-8qx1DL/2Wsrrij2TWX5UzvEaYOFVndR7BogdpOyF4ocMfnfkw28qt8ULkXD9Tef0bLvh3TpnSAljDC7uyniEuQ==" crossorigin="anonymous"></script>
    <!-- <script src="https://ace.c9.io/lib/ace/keyboard/vscode.js"></script> -->

    
    <script src="{{url('defaults/vue.min.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue-loading-overlay@3"></script>
    <link href="https://cdn.jsdelivr.net/npm/vue-loading-overlay@3/dist/vue-loading.css" rel="stylesheet">
    <script src="https://unpkg.com/vue-select@3.0.0"></script>
    <link rel="stylesheet" href="https://unpkg.com/vue-select@3.0.0/dist/vue-select.css">
    <script src="{{url('defaults/axios.min.js')}}"></script>
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.21.2/min/vs/editor/editor.main.min.css" integrity="sha512-9uX8QlyL0SosYXO3oNqyiXdnmhtWk22wutqEzGR53Bezc+yqYVvFukBAOW97fPx/3Dxdul77zW27GwHRzdYfMg==" crossorigin="anonymous" /> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.21.2/min/vs/editor/editor.main.js" integrity="sha512-wT1GtkfpGl0hLM5MbJIobnwU89WdvSFTKM90FgguAHyTR763v6i5zRgVyCUBiohRyLvv0+KBRc+iOpwgXLZabQ==" crossorigin="anonymous"></script>
    <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap/dist/css/bootstrap.min.css" />
    <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.min.css" />
    <script src="//unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue-splitpane@1.0.6/dist/vue-split-pane.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vuex/2.1.1/vuex.min.js"></script>
    <style>
        .no-select {
            -webkit-touch-callout: none; /* iOS Safari */
                -webkit-user-select: none; /* Safari */
                -khtml-user-select: none; /* Konqueror HTML */
                -moz-user-select: none; /* Old versions of Firefox */
                    -ms-user-select: none; /* Internet Explorer/Edge */
                        user-select: none; /* Non-prefixed version, currently
                                            supported by Chrome, Edge, Opera and Firefox */
            }
        .ace_prompt_container {
            z-index:999999999 !important;
        }
        .ace-monokai .ace_storage {
            color: #66D9EF !important;
        }
        .ace-monokai .ace_support.ace_function {
            color: #66ffef;
        }
        .ace_support .ace_php_tag{            
            color: #66D9EF !important;
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
    <!-- Load the following for BootstrapVueIcons support -->
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
                    <b-icon :icon="item.icon" style="margin-right:5px;"></b-icon>{{ item.name }}
                    <b-icon size="sm" icon="pencil-square" style="margin-left:5px;" v-if="$store.state.activeEditorTitle==(item.name+'-'+item.src)"></b-icon>
                    <b-spinner variant="danger" type="grow" small label="Active" style="max-width:10px;max-height:10px;" v-if="$store.state.activeEditorTitle==(item.name+'-'+item.src)"></b-spinner>
                </span>
            </span>
        </div>
        <ul v-show="isOpen||($store.state.activeEditorTitle).split('-')[1]==(item.src)" v-if="isFolder" style="list-style: none;padding-left:15px;">
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
<body class="bg-dark-monokai" style="overflow:hidden;">
<div id="app" >
    <split-pane @resize="resize" :min-percent='0' :default-percent="$store.state.sidebarLeft" split="vertical">
      <template ref="leftPanel" slot="paneL">
        <div style="z-index:1;min-width:280px;" class="text-white col-md-12 col-sm-12 col-xs-12">
                <b-row class="mt-md-2">
                    <b-col md="6" class="ml-md-2 mr-md-0" style="padding-right:1px;">
                        <b-form-input size="sm" placeholder='search' v-model="searchData" @input="search" style="background-color: #34352f !important;color:white !important;"></b-form-input>
                    </b-col>
                    <b-col style="padding-left:0px;">
                        <b-btn title="add new" class="bg-dark-monokai" size="sm" style="margin-top:2px;margin-right:0px;" @click="add_new">
                            <b-icon icon="plus-square"></b-icon>
                        </b-btn>
                    </b-col>
                </b-row>
                <div style="overflow: auto;height:100%;">
                <div class='ml-5' v-if="treeData.length==0">
                    <b-spinner style="width: 2rem; height: 2rem;margin-top:5%" label="Large Spinner"></b-spinner>
                </div>
                    <ul id="demo" style="list-style-type: none;margin: 3px;padding: 0;font-size:12px;">
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
            <b-tabs active-nav-item-class="font-weight-bold monokai-active-tab"
                    content-class="mt-0" style="width:100%;" nav-class='monokai-inactive-tab' @input="changeTab">
                <b-tab  v-for="(item,index) in $store.state.activeEditors" :active="index==$store.state.activeEditorIndex">
                    <template #title style="font-size:9px;">
                        <!-- <b-spinner type="grow" small label="Active" v-if="index==$store.state.activeEditorIndex"></b-spinner> -->
                        <b-icon size="sm" :icon="item.icon" style="max-height:15px;"></b-icon>
                        <small style="font-size:12px;color:#ccccc7 !important;" :title="item.jenis">{{item.title}} 
                            <!-- <span style='font-size:10px;'>{{item.jenis}}</span> -->
                        </small>
                        <b-btn title="Close" class="monokai-inactive-tab" size="sm" 
                            @click="$store.commit('removeActiveEditors',{ index:index, item:item})"
                            style="padding:0px !important;font-size:12px;margin:auto;"/>
                            &nbsp;&nbsp;x&nbsp;&nbsp;
                        </b-btn>
                    </template>
                    <div style="overflow: auto;height:100%;">
                        <vue-ace-editor 
                            v-model:value ="item.value" 
                            v-bind:options="item" 
                            :id="'editor_'+index">
                        </vue-ace-editor>      
                    </div>
                    <b-btn pill 
                        @click="$store.dispatch(item.action, item)"
                        :class="item.action=='alter'?'bg-warning':'bg-danger'" 
                        style="z-index:999999;position:fixed;right:13px;bottom:30px;" 
                        v-if="item.action" :title="item.action" size="sm">
                        <b-icon icon="lightning-fill" ></b-icon>
                    </b-btn>
                </b-tab>
            </b-tabs>
        </div>
      </template>
    </split-pane>
</div>
@endverbatim
<script>

Vue.use(VueLoading);
Vue.component('loading', VueLoading)
Vue.component("tree-item", {
    template: "#item-template",
    props: {
        item: Object
    },
    data: function() {
        return {
        isOpen: false
        };
    },
    computed: {
        isFolder: function() {
        return this.item.children && this.item.children.length;
        }
    },
    methods: {
        toggle: function() {
            if (this.isFolder) {
                this.isOpen = !this.isOpen;
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
    //  simplified model handling using `value` prop and `input` event for $emit 
    props:['value','id','options'],
    template:`
        <div :id="id ? id: $options._componentTag +'-'+ _uid" 
             :class="$options._componentTag">
            <slot></slot>
        </div>
    `,

    watch:{
        value() { 
            //  two way binding – emit changes to parent
            this.$emit('input', this.value);
            
            //  update value on external model changes
            if(this.oldValue !== this.value){ 
                this.editor.setValue(this.value, 1); 
            }
        }
    },
   
    mounted(){
        //  editor
        this.editor = window.ace.edit(this.$el.id);
        let me = this;
        // let myeditor = this.editor;
        //  deprecation fix
        this.editor.$blockScrolling = Infinity;        

        //  ignore doctype warnings
        const session = this.editor.getSession();
        session.on("changeAnnotation", () => {
            const a = session.getAnnotations();
            const b = a.slice(0).filter( (item) => item.text.indexOf('DOC') == -1 );
            if(a.length > b.length) session.setAnnotations(b);
        });

        //  editor options 
        //  https://github.com/ajaxorg/ace/wiki/Configuring-Ace
        this.options = this.options || {};
        
        //  opinionated option defaults
        this.options.maxLines = this.options.maxLines || Infinity;
        this.options.printMargin = this.options.printMargin || false;      
        this.options.highlightActiveLine = this.options.highlightActiveLine || false;

        //  hide cursor 
        if(this.options.cursor === 'none' || this.options.cursor === false){
            this.editor.renderer.$cursorLayer.element.style.display = 'none';
            delete this.options.cursor; 
        }

        //  add missing mode and theme paths 
        if(this.options.mode && this.options.mode.indexOf('ace/mode/')===-1) {
            this.options.mode = `ace/mode/${this.options.mode}`;
        }
        if(this.options.theme && this.options.theme.indexOf('ace/theme/')===-1) {
            this.options.theme = `ace/theme/${this.options.theme}`;
        }
        this.editor.setOptions(this.options);
        
        
        //  set model value 
        //  if no model value found – use slot content
        if(!this.value || this.value === ''){
            this.$emit('input', this.editor.getValue());
        } else {
            this.editor.setValue(this.value, -1);
        }        
        //  editor value changes   
        this.editor.on('change', () => {
            //  oldValue set to prevent internal updates
             this.value = this.oldValue = this.editor.getValue();
            //  console.log(this.editor.getValue().split("\n").length)
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
    methods:{ editor(){ return this.editor } }
};


Vue.component('split-pane', SplitPane.SplitPane);
Vue.component('v-select', VueSelect.VueSelect);
vm = new Vue({
    el: '#app',
    'store': new Vuex.Store(
        {
            state: {
                sidebar: false,
                sidebarLeft:25,
                activeEditorIndex:0,
                activeEditorTitle:"-",
                activeEditors:[{
                        action:'migrate',
                        mode:'php',
                        title:"New File",
                        value:'',
                        theme: 'monokai',
                        fontSize: 12,
                        fontFamily: 'monospace',
                        highlightActiveLine: true,
                        
                        // highlightGutterLine: true,
                        // emableLiveAutocompletion:true,
                        enableBasicAutocompletion:true,
                        maxLines:parseInt(window.innerHeight/15.2),
                        minLines:parseInt(window.innerHeight/15.2+25)
                }],
                modelList:[]
            },
            mutations: {
                changeTab(state,index){
                    if(state.activeEditorIndex==index){return;}
                    state.activeEditorIndex=index;
                    let item = state.activeEditors.find((dt,i)=>i==index);
                    state.activeEditorTitle=item.jenis+'-'+item.title;
                },
                sidebarChange (state,val) {
                    state.sidebar=val;
                },
                sidebarLeftChange (state,val) {
                    state.sidebarLeft=val;
                },
                addActiveEditors(state,objVal){
                    let ketemu = state.activeEditors.findIndex(dt=>{ return dt.title==objVal.title&&dt.jenis==objVal.jenis;} );
                    if(ketemu>-1){
                        state.activeEditors[ketemu]=objVal.value;
                        state.activeEditorIndex = ketemu;
                        return;
                    }
                    state.activeEditors.push(objVal);
                    state.activeEditorIndex = state.activeEditors.length-1;
                },
                removeActiveEditors(state,dt){
                    let confirm = window.confirm(`Close [${dt.item.jenis}] ${dt.item.title}?`);
                    if(confirm){
                        state.activeEditors = state.activeEditors.filter((data,i)=>{ return i!=dt.index;});
                    }
                }
            },
            actions: {
                saveOnline ({ commit, state }, editor) {
                    window.aku=editor;
                    console.log(editor)
                },
                alter({commit,state}, item){
                    let confirm = window.confirm(`ALTER ${item.title} ?`);
                    if(confirm){
                        state.activeEditors = state.activeEditors.filter((dt,i)=>i!=index);
                    }
                },
                migrate({commit,state}, item){
                    let confirm = window.confirm(`WARNING!! Migrate ${item.title}?`);
                    if(confirm){
                        state.activeEditors = state.activeEditors.filter((dt,i)=>i!=index);
                    }
                },
                getModels({commit,state}){
                    let loader = Vue.$loading.show({
                        color: 'grey',loader: 'dots',
                    },{
                        
                    });
                    axios({
                        url         : "{{url('laradev/models')}}",
                        credentials : true,
                        // method      : data.method,
                        // data        : data.body,
                        headers     : {
                            laradev:"quantumleap150671"
                        }
                    }).then(response => {
                        state.modelList = response.data;
                    }).catch(error => {
                        console.log(error)
                    }).then(function () {
                        loader.hide();
                    });  ;
                }
            }
        }
    ),
    components:{ 'vue-ace-editor': VueAceEditor },
    data:{
        editorcontent: 'tes',
        searchData:""
        //  https://github.com/ajaxorg/ace/wiki/Configuring-Ace
    },
    created(){
        this.$store.dispatch('getModels');
    },
    computed:{
        treeData:function(){
            var treeData = [];
            let models=this.$store.state.modelList.models;
            // console.log(models)
            for(let i in models){
                if(this.searchData!="" && this.searchData!==null){
                    if(!(models[i].file).includes(this.searchData)){
                        continue;
                    }
                }
                let name = 'migration';
                let icon = 'table';
                let children = [
                    { name: 'Migration', icon:"card-checklist",src:(models[i].file).split(".")[0] },
                    { name: "Alter",icon:"bookmark-plus",src:(models[i].file).split(".")[0] },
                    { name: "Basic Model",icon:"check2-square",src:(models[i].file).split(".")[0] },
                    { name: "Custom Model",icon:"code-square",src:(models[i].file).split(".")[0] }
                ];
                if((models[i].file).includes("_after_")){
                    name='trigger'; icon = 'lightning-fill';
                    children.splice(1,1)
                }else if(models[i].view){
                    children.splice(1,1)
                    name='view'; icon = 'eye-fill';
                }

                treeData.push({
                    name: (models[i].file).split(".")[0],
                    icon: icon,
                    children: children
                });
            }
            return treeData;
        }
    },
    methods: {
        changeTab(index){
            this.$store.commit('changeTab',index);
        },
        add_new(){
            var modul = prompt("Nama Migration (standard : (3)modul_(3)submodul_processname):", "");
            if (modul == null || modul == "") {
            } else {
                
            }
        },
        search(e){
            // console.log(e)
        },
        resize(a){
            this.$store.commit('sidebarLeftChange',a);
        },
        addFile: function(item,e) {
            let itemLengkap = this.treeData.find(dt=>dt.name==item.src);
            let icon,action,endpoint;
            let me = this;
            if(item.name=='Migration'){
                icon='lightning'; action='migrate'; endpoint="/migrations/"+item.src;
            }else if(item.name=='Alter'){
                icon='shuffle'; action='alter';endpoint="/alter/"+item.src;
            }else if( (item.name).toLowerCase().includes('custom')){
                icon='file-code';action="";endpoint="/models/"+item.src;
            }else if((item.name).toLowerCase().includes('basic')){
                icon='file-check';action="";endpoint="/models/"+item.src;
            }else{
                icon='list-check';action="";
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
                    laradev:"quantumleap150671"
                }
            }).then(response => {           
                me.$store.commit('addActiveEditors',{
                        title:itemLengkap.name,
                        jenis:item.name,
                        value:item.name.includes('Model')?response.data.text:response.data,
                        icon: icon,
                        readOnly:(item.name).toLowerCase().includes('basic'),
                        action:action,
                        mode:'php',
                        theme: 'monokai',
                        fontSize: 12,
                        fontFamily: 'monospace',
                        highlightActiveLine: true,
                        enableBasicAutocompletion:true,
                        maxLines:parseInt(window.innerHeight/15.2),
                        minLines:parseInt(window.innerHeight/15.2+15)
                })
            }).catch(error => {
                console.log(error)
            }).then(function () {
                loader.hide();
            });
        },
        openFile: function(item) {
            console.log(item)
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
</script>
</body>
</html>