
/**
 *
 * COMPONENTS
 *
 */

// Profiles list
Vue.component('profiles-list', {
    mixins: [utilFuncs, mainFuncs],
    data: function () {
        return {
            list: [],
        }
    },
    template: `
<div class="row">
    <div class="col-lg-6" v-for="item in list">
        <div class="card-box">
            <span class="badge bg-soft-success text-success float-right" v-if="item.active == \'Y\'">{{ $t("page.SP_CI_PROFILES_ACTIVE_Y") }}</span>
            <span class="badge bg-soft-danger text-danger float-right" v-if="item.active != \'Y\'">{{ $t("page.SP_CI_PROFILES_ACTIVE_N") }}</span>
            <h4 class="header-title"><a href="#" class="text-dark">{{item.name}}</a></h4>
            <!--<p class="text-muted font-13 sp-line-2">With supporting text below as a natural lead-in to additional contenposuere erat a ante.</p>-->
            <a :href="'sprod_integr_profiles.php?id=' + item.id + '&lang=ru'" target="_top" class="btn btn-info waves-effect mt-2"><i class="mdi mdi-pencil"></i> {{ $t("page.SP_CI_PROFILES_BTN_EDIT") }}</a>
        </div> <!-- end card-box -->
    </div> <!-- end col -->
    <div class="col-lg-6">
        <button type="button" class="btn btn-info ml-3 mb-3" @click="addItem"><i class="mdi mdi-plus"></i> {{ $t("page.SP_CI_PROFILES_BTN_ADD") }}</button>
    </div> <!-- end col -->
</div>
`,
    methods: {
        // List update
        updateList: function (callback) {
            this.$emit('load_start');
            this.ajaxReq('profiles_list', 'post', {
                id: this.$profile_id,
            }, (response) => {
                this.list = response.data.list;
            }, (response) => {
            }, (response) => {
                // Callback success
                if (typeof callback === 'function') {
                    callback(response);
                }
                this.$emit('load_stop');
            });
        },
        // Element add
        addItem: function (callback) {
            this.$emit('load_start');
            this.ajaxReq('profiles_add', 'post', {
                id: this.$profile_id,
            }, (response) => {
                this.updateList();
            }, (response) => {
            }, (response) => {
                // Callback success
                if (typeof callback === 'function') {
                    callback(response);
                }
                this.$emit('load_stop');
            });
        },
    },
    mounted() {
        this.updateList();
    },
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
});
