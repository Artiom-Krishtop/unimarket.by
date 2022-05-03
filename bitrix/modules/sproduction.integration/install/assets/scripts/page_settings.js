
/**
 *
 * MIXINS
 *
 */

var componentsFuncs = {
    mixins: [mainFuncs],
    methods: {
        blockSaveData: function (code, callback) {
            this.state.active = false;
            this.ajaxReq('settings_'+code+'_save', 'post', {
                fields: this.fields,
            }, (response) => {
                // Blocks update
                this.$emit('block_update', code);
            }, (response) => {
            }, (response) => {
                // Callback success
                if (typeof callback === 'function') {
                    callback(response);
                }
            });
        },
    },
    mounted() {
        // Blocks update (ordering data)
        this.$root.$on('blocks_update_before', (calling_block) => {
            this.state.active = false;
        });
        // Blocks update (data is received)
        this.$root.$on('blocks_update', (data, calling_block) => {
            this.state = data.blocks[this.code].state;
            this.fields = data.blocks[this.code].fields;
            this.info = data.blocks[this.code].info;
            // let res = parse_url('http://example.com:3000/pathname/?search=test#hash');
            // console.log(res.protocol);
            // console.log(res.hostname);
        });
    },
};


/**
 *
 * COMPONENTS
 *
 */

// Connection settings
Vue.component('settings-connect', {
    props: [],
    data: function () {
        return {
            code: 'connect',
            state: {
                display: true,
                active: false,
            },
            fields: {
                site: '',
                portal: '',
                app_id: '',
                secret: '',
                auth_link: '',
            },
            info: {
                has_cred: false,
            },
        }
    },
    computed: {
        app_link: function () {
            return this.fields.site + '/bitrix/sprod_integr_auth.php'
        },
        app_create_link: function () {
            let link = ''
            if (this.fields.portal && (!this.fields.app_id || !this.fields.secret)) {
                link = this.fields.portal + '/devops/section/standard/'
            }
            return link
        },
    },
    methods: {
        credReset: function (code) {
            if (confirm(this.$t("page.SP_CI_SETTINGS_RESET_CONN_WARNING"))) {
                this.state.active = false;
                this.ajaxReq('settings_' + code + '_reset', 'post', {
                    id: this.$profile_id,
                }, (response) => {
                    // Blocks update
                    this.$emit('block_update', code);
                }, (response) => {
                }, (response) => {
                    // Callback success
                    if (typeof callback === 'function') {
                        callback(response);
                    }
                });
            }
        },
    },
    watch: {
        'fields.site': function (new_value) {
            this.fields.site = new_value.replace(/\/$/ig, "");
        },
        'fields.portal': function (new_value) {
            this.fields.portal = new_value.replace(/\/$/ig, "");
        },
    },
    template: `
<div class="row" v-bind:class="{ \'block-disabled\': state.active == false }" v-if="state.display">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h4 class="header-title">{{ $t("page.SP_CI_SETTINGS_CONNECT_TITLE") }}</h4>
                <p class="sub-header">{{ $t("page.SP_CI_SETTINGS_CONNECT_SUBTITLE") }}</p>
                <div class="form-group mb-3">
                    <label for="settings_site">{{ $t("page.SP_CI_SETTINGS_CONNECT_SITE") }}</label>
                        <input type="text" class="form-control" id="settings_site" placeholder="https://site.ru" v-model="fields.site">
                    <b-tooltip target="settings_site" placement="bottom">{{ $t("page.SP_CI_SETTINGS_CONNECT_SITE_HINT") }}</b-tooltip>
                </div>
                <div class="form-group mb-3" v-if="fields.site">
                    <label for="settings_portal">{{ $t("page.SP_CI_SETTINGS_CONNECT_PORTAL") }}</label>
                        <input type="text" class="form-control" id="settings_portal" placeholder="https://portal.bitrix24.ru" v-model="fields.portal">
                    <b-tooltip target="settings_portal" placement="bottom">{{ $t("page.SP_CI_SETTINGS_CONNECT_PORTAL_HINT") }}</b-tooltip>
                </div>
                <div class="form-group mb-3" v-if="fields.site">
                    <label for="settings_app_link">{{ $t("page.SP_CI_SETTINGS_CONNECT_APP_LINK") }}</label>
                    <input type="text" id="settings_app_link" class="form-control" readonly :value="app_link">
                    <b-tooltip target="settings_app_link" placement="bottom">{{ $t("page.SP_CI_SETTINGS_CONNECT_APP_LINK_HINT") }}</b-tooltip>
                    <a class="btn btn-info mt-1" v-if="app_create_link" :href="app_create_link" target="_blank">{{ $t("page.SP_CI_SETTINGS_CONNECT_APP_CREATE_LINK") }}</a>
                </div>
                <div class="form-group mb-3" v-if="fields.site">
                    <label for="settings_app_id">{{ $t("page.SP_CI_SETTINGS_CONNECT_APP_ID") }}</label>
                    <input type="text" id="settings_app_id" class="form-control" v-model="fields.app_id">
                </div>
                <div class="form-group mb-3" v-if="fields.site">
                    <label for="settings_secret">{{ $t("page.SP_CI_SETTINGS_CONNECT_SECRET") }}</label>
                    <input type="text" id="settings_secret" class="form-control" v-model="fields.secret">
                </div>
                <!--<a class="btn btn-info mt-1" v-if="app_adit_link" :href="app_adit_link" target="_blank">Посмотреть приложение</a>-->
                <button class="btn btn-success" @click="blockSaveData(code)">
                    {{ $t("page.SP_CI_SETTINGS_SAVE") }}
                </button>
                <button v-if="info.has_cred" class="btn btn-sm btn-light float-right" @click="credReset(code)" v-b-tooltip.hover :title="$t('page.SP_CI_SETTINGS_RESET_CONN_HINT')">
                    {{ $t("page.SP_CI_SETTINGS_RESET_CONN") }}
                </button>
                <div class="alert alert-warning border-0 mt-3" role="alert" v-if="fields.auth_link">
                    {{ $t("page.SP_CI_SETTINGS_CONNECT_AUTH_LINK") }}<br>
                    <a target="_top" :href="fields.auth_link">{{fields.auth_link}}</a>
                </div>
            </div> <!-- end card-body -->
        </div> <!-- end card -->
        <div class="card">
            <div class="card-body">
                <p class="mb-0" v-html="$t('page.SP_CI_SETTINGS_CONNECT_STATUS_LINK')"></p>
            </div> <!-- end card-body -->
        </div> <!-- end card -->
    </div><!-- end col -->
    <div class="col-md-6">
        <div class="card-box ribbon-box">
            <div class="ribbon ribbon-info float-left"><i class="mdi mdi-access-point mr-1"></i> {{ $t("page.SP_CI_SETTINGS_INFO") }}</div>
            <h5 class="text-info float-right mt-0">{{ $t("page.SP_CI_SETTINGS_CONNECT_INFO_TITLE") }}</h5>
            <div class="ribbon-content">
                <p class="mb-0" v-html="$t('page.SP_CI_SETTINGS_CONNECT_INFO_TEXT')"></p>
            </div>
        </div>
    </div>
</div>
`,
    mixins: [utilFuncs, componentsFuncs, mainFuncs],
});

// Params of synchronization
Vue.component('settings-sync', {
    props: [],
    components: {
        'vuejs-datepicker': vuejsDatepicker,
    },
    data: function () {
        return {
            code: 'sync',
            state: {
                display: true,
                active: false,
            },
            fields: {
                source_id: '',
                source_name: '',
                direction: 'stoc',
                start_date: '',
            },
            datepicker: {
                ru: vdp_translation_ru.js,
                disabled: {
                    from: new Date(),
                },
                format: 'dd.MM.yyyy',
            }
        }
    },
    template: `
<div class="row" v-bind:class="{ \'block-disabled\': state.active == false }" v-if="state.display">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h4 class="header-title">{{ $t("page.SP_CI_SETTINGS_SYNC_TITLE") }}</h4>
                <p class="sub-header">{{ $t("page.SP_CI_SETTINGS_SYNC_SUBTITLE") }}</p>
                <div class="form-group mb-3">
                    <label for="sync_source_id">{{ $t("page.SP_CI_SETTINGS_SYNC_SOURCE_ID") }}</label>
                    <input type="text" id="sync_source_id" class="form-control" v-model="fields.source_id" v-b-tooltip.hover :title="$t('page.SP_CI_SETTINGS_SYNC_SOURCE_ID_TOOLTIP')">
                </div>
                <div class="form-group mb-3">
                    <label for="sync_source_name">{{ $t("page.SP_CI_SETTINGS_SYNC_SOURCE_NAME") }}</label>
                    <input type="text" id="sync_source_name" class="form-control" v-model="fields.source_name" v-b-tooltip.hover :title="$t('page.SP_CI_SETTINGS_SYNC_SOURCE_NAME_TOOLTIP')">
                </div>
                <div class="form-group mb-3">
                    <label for="example-date">{{ $t("page.SP_CI_SETTINGS_SYNC_DIRECTION") }}</label>
                    <div class="radio radio-info mb-2">
                        <input type="radio" v-model="fields.direction" id="sync_direction_stoc" value="stoc">
                        <label for="sync_direction_stoc">{{ $t("page.SP_CI_SETTINGS_SYNC_DIRECTION_STOC") }}</label>
                    </div>
                    <div class="radio radio-info mb-2">
                        <input type="radio" v-model="fields.direction" id="sync_direction_full" value="full">
                        <label for="sync_direction_full">{{ $t("page.SP_CI_SETTINGS_SYNC_DIRECTION_FULL") }}</label>
                    </div>
                </div>
                <div class="form-grou mb-3">
                    <label for="example-date">{{ $t("page.SP_CI_SETTINGS_SYNC_START_DATE") }}</label>
                    <vuejs-datepicker v-model="fields.start_date" :language="datepicker.ru" :format="datepicker.format" :bootstrap-styling="true" :disabled-dates="datepicker.disabled"></vuejs-datepicker>
                </div>
                <div class="form-group">
                </div>
                <button class="btn btn-success" @click="blockSaveData(code)">
                    {{ $t("page.SP_CI_SETTINGS_SAVE") }}
                </button>
            </div> <!-- end card-body -->
        </div> <!-- end card -->
    </div><!-- end col -->
</div>
`,
    mixins: [utilFuncs, componentsFuncs],
});

// Synchronization active
Vue.component('settings-active', {
    props: [],
    data: function () {
        return {
            code: 'active',
            state: {
                display: true,
                active: false,
            },
            fields: {
                active: '',
            },
        }
    },
    template: `
<div class="row" v-bind:class="{ \'block-disabled\': state.active == false }" v-if="state.display">
    <div class="col-md-6">
        <div class="alert alert-info">
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="settings_active" @change="blockSaveData(code)" v-model="fields.active" value="Y">
                <label class="custom-control-label" for="settings_active">{{ $t("page.SP_CI_SETTINGS_ACTIVE_LABEL") }}</label>
            </div>
        </div>
    </div><!-- end col -->
</div>
`,
    mixins: [utilFuncs, componentsFuncs],
});

// Profiles warning
Vue.component('profiles-warn', {
    props: [],
    data: function () {
        return {
            code: 'profiles',
            state: {
                display: false,
            },
            fields: {},
        }
    },
    template: `
<div class="row" v-if="state.display">
    <div class="col-md-6">
        <b-alert show variant="warning" v-html="$t('page.SP_CI_SETTINGS_PROFILES_WARNING')"></b-alert>
    </div><!-- end col -->
</div>
`,
    mixins: [utilFuncs, componentsFuncs],
});

// Additional synchronization
Vue.component('settings-add_sync', {
    props: [],
    data: function () {
        return {
            code: 'add_sync',
            state: {
                display: true,
                active: false,
            },
            fields: {
                add_sync_schedule: '',
            },
        }
    },
    template: `
<div class="row" v-bind:class="{ \'block-disabled\': state.active == false }" v-if="state.display">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h4 class="header-title">{{ $t("page.SP_CI_SETTINGS_ADD_SYNC_TITLE") }}</h4>
                <p class="sub-header">{{ $t("page.SP_CI_SETTINGS_ADD_SYNC_SUBTITLE") }}</p>
                <div class="row mb-1">
                    <div class="col-sm-12">
                        <div class="radio mb-2">
                            <input v-model="fields.add_sync_schedule" type="radio" name="add_sync_schedule" id="add_sync_schedule_disabled" value="" checked>
                            <label for="add_sync_schedule_disabled">
                                {{ $t("page.SP_CI_SETTINGS_ADD_SYNC_SCHEDULE_DISABLED") }}
                            </label>
                        </div>
                        <div class="radio radio-info mb-2">
                            <input v-model="fields.add_sync_schedule" type="radio" name="add_sync_schedule" id="add_sync_schedule_1h" value="1h">
                            <label for="add_sync_schedule_1h">
                                {{ $t("page.SP_CI_SETTINGS_ADD_SYNC_SCHEDULE_1H") }}
                            </label>
                        </div>
                        <div class="radio radio-info mb-2">
                            <input v-model="fields.add_sync_schedule" type="radio" name="add_sync_schedule" id="add_sync_schedule_1d" value="1d">
                            <label for="add_sync_schedule_1d">
                                {{ $t("page.SP_CI_SETTINGS_ADD_SYNC_SCHEDULE_1D") }}
                            </label>
                        </div>
                    </div> <!-- end col -->
                </div> <!-- end row-->
                <button class="btn btn-success" @click="blockSaveData(code)">
                    {{ $t("page.SP_CI_SETTINGS_SAVE") }}
                </button>
            </div> <!-- end card-body -->
        </div> <!-- end card -->
    </div><!-- end col -->
    <div class="col-md-6">
        <div class="card-box ribbon-box">
            <div class="ribbon ribbon-info float-left"><i class="mdi mdi-access-point mr-1"></i> {{ $t("page.SP_CI_SETTINGS_INFO") }}</div>
            <h5 class="text-info float-right mt-0">{{ $t("page.SP_CI_SETTINGS_ADD_SYNC_INFO_TITLE") }}</h5>
            <div class="ribbon-content">
                <p class="mb-0" v-html="$t('page.SP_CI_SETTINGS_ADD_SYNC_INFO_TEXT')"></p>
            </div>
        </div>
    </div>
</div>
`,
    mixins: [utilFuncs, componentsFuncs],
});

// Manual synchronization
Vue.component('settings-man_sync', {
    props: [],
    data: function () {
        return {
            code: 'man_sync',
            state: {
                display: true,
                active: false,
            },
            fields: {
                man_sync_period: '',
                man_sync_only_new: '',
            },
            progress: 0,
            max: 100,
        }
    },
    methods: {
        runSync: function () {
            this.progress = 1;
            this.runSyncStep(0);
        },
        runSyncStep: function (next_item) {
            axios
                .post('/bitrix/admin/sprod_integr_sync.php', {
                    next_item: next_item
                })
                .then(response => {
                    if (response.data.status == 'success') {
                        if (response.data.count) {
                            this.max = response.data.count;
                            this.progress = response.data.next_item;
                            if (this.progress < this.max) {
                                this.runSyncStep(response.data.next_item);
                            } else {
                                setTimeout(() => {
                                    this.progress = 0;
                                }, 1000);
                            }
                        }
                        else {
                            this.progress = 0;
                        }
                    }
                    else {
                        console.log(response.data);
                    }
                })
                .catch(error => {
                    console.log(error);
                });
        },
    },
    template: `
<div class="row" v-bind:class="{ \'block-disabled\': state.active == false }" v-if="state.display">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h4 class="header-title">{{ $t("page.SP_CI_SETTINGS_MAN_SYNC_TITLE") }}</h4>
                <p class="sub-header">{{ $t("page.SP_CI_SETTINGS_MAN_SYNC_SUBTITLE") }}</p>
                <div class="form-group mb-3">
                    <label for="simpleinput">{{ $t("page.SP_CI_SETTINGS_MAN_SYNC_SYNC_PERIOD") }}</label>
                    <b-form-select v-model="fields.man_sync_period" @change="blockSaveData(code)">
                        <option value="">{{ $t("page.SP_CI_SETTINGS_MAN_SYNC_SYNC_PERIOD_ALL") }}</option>
                        <option value="1d">{{ $t("page.SP_CI_SETTINGS_MAN_SYNC_SYNC_PERIOD_1D") }}</option>
                        <option value="1w">{{ $t("page.SP_CI_SETTINGS_MAN_SYNC_SYNC_PERIOD_1W") }}</option>
                        <option value="1m">{{ $t("page.SP_CI_SETTINGS_MAN_SYNC_SYNC_PERIOD_1M") }}</option>
                        <option value="3m">{{ $t("page.SP_CI_SETTINGS_MAN_SYNC_SYNC_PERIOD_3M") }}</option>
                    </b-form-select>
                </div>
                <div class="form-group mb-3">
                    <div class="checkbox checkbox-info">
                        <input type="checkbox" id="settings_man_sync_only_new" v-model="fields.man_sync_only_new" value="" @change="blockSaveData(code)">
                        <label for="settings_man_sync_only_new">{{ $t("page.SP_CI_SETTINGS_MAN_SYNC_ONLY_NEW") }}</label>
                    </div>
                </div>
                <button class="btn btn-blue" @click="runSync">
                    {{ $t("page.SP_CI_SETTINGS_MAN_SYNC_RUN") }} <i class="fas fa-arrow-right"></i>
                </button>
                <b-progress :value="progress" :max="max" variant="info" class="mt-3" animated></b-progress>
            </div> <!-- end card-body -->
        </div> <!-- end card -->
    </div><!-- end col -->
    <div class="col-md 6">   
        <div class="card-box ribbon-box">
            <div class="ribbon ribbon-info float-left"><i class="mdi mdi-access-point mr-1"></i> {{ $t("page.SP_CI_SETTINGS_INFO") }}</div>
            <h5 class="text-info float-right mt-0">{{ $t("page.SP_CI_SETTINGS_MAN_SYNC_TITLE") }}</h5>
            <div class="ribbon-content">
                <p class="mb-0" v-html="$t('page.SP_CI_SETTINGS_MAN_SYNC_TEXT')"></p>
            </div>
        </div>    
    </div>
</div>
`,
    mixins: [utilFuncs, componentsFuncs],
});


/**
 *
 * VUE APP
 *
 */

const i18n = new VueI18n({
    locale: 'ru',
    messages,
});

var app = new Vue({
    el: '#app',
    i18n,
    mixins: [utilFuncs, mainFuncs],
    data: {
        main_error: '',
    },
    methods: {
        // Blocks update
        updateBlocks: function (calling_block) {
            // Blocks update
            this.$emit('blocks_update_before', calling_block);
            this.ajaxReq('settings_get', 'get', {
                id: this.$profile_id,
            }, (response) => {
                this.$emit('blocks_update', response.data, calling_block);
            }, (response) => {
            }, (response) => {
                // Callback success
                if (typeof callback === 'function') {
                    callback(response);
                }
            });
        },
    },
    mounted() {
        this.updateBlocks();
    },
});
