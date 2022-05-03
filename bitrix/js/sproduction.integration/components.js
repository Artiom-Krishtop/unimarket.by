
/**
 *
 * MIXINS
 *
 */

var utilFuncs = {
    methods: {
        getReqPath: function (action) {
            return '/bitrix/admin/sprod_integr_ajax.php?action=' + action;
        },
    },
};

var mainFuncs = {
    data: function () {
        return {
            loader_counter: 0,
            errors: [],
            warnings: [],
        }
    },
    methods: {
        ajaxReq: function (action, type, params, success, failure, callback) {
            if (type == 'post') {
                axios
                    .post(this.getReqPath(action), params)
                    .then(response => {
                        if (response.data.status == 'ok') {
                            // Callback success
                            if (typeof success === 'function') {
                                success(response);
                            }
                        } else {
                            // Callback failure
                            if (typeof failure === 'function') {
                                failure(response);
                            }
                            console.log('Error: ' + response.data.message);
                        }
                        // Callback for all
                        if (typeof callback === 'function') {
                            callback(response);
                        }
                    })
                    .catch(error => {
                        console.log(error);
                    });
            }
            else {
                axios
                    .get(this.getReqPath(action))
                    .then(response => {
                        if (response.data.status == 'ok') {
                            // Callback success
                            if (typeof success === 'function') {
                                success(response);
                            }
                        } else {
                            // Callback failure
                            if (typeof failure === 'function') {
                                failure(response);
                            }
                            console.log('Error: ' + response.data.message);
                        }
                        // Callback for all
                        if (typeof callback === 'function') {
                            callback(response);
                        }
                    })
                    .catch(error => {
                        console.log(error);
                    });
            }
        },
        getReqPath: function (action) {
            return '/bitrix/admin/sprod_integr_ajax.php?action=' + action;
        },
        startLoadingInfo: function () {
            this.loader_counter++;
        },
        stopLoadingInfo: function () {
            this.loader_counter--;
            if (this.loader_counter < 0) {
                this.loader_counter = 0;
            }
        },
    },
    mounted() {
        // Check module state
        axios
            .get(this.getReqPath('main_check'))
            .then(response => {
                if (response.data.errors.length) {
                    this.errors = response.data.errors;
                }
                if (response.data.warnings.length) {
                    this.warnings = response.data.warnings;
                }
            })
            .catch(error => {
                console.log(error);
            });
    },
};


/**
 *
 * VUE COMPONENTS
 *
 */

// Error
Vue.component('main-errors', {
    props: ['errors', 'warnings'],
    template: `
    <div class="main-errors">
        <b-alert show variant="danger" v-for="text in errors" v-html="text"></b-alert>
        <b-alert show variant="warning" v-for="text in warnings" v-html="text"></b-alert>
    </div>
`,
});

// Loader
Vue.component('loader', {
    props: ['counter'],
    template: `
    <div class="loader float-right" v-if="counter">
        <div class="spinner-border text-info m-2" role="status"></div>
    </div>
`,
});
