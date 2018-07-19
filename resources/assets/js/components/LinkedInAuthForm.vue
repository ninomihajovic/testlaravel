<template>
    <form class="my-3" @submit.prevent="onSubmit">
        <div class="form-group row">
            <label for="linkedin_email" class="col-sm-3 col-form-label">Email address</label>
            <div class="col-sm-9">
                <input type="email" id="linkedin_email" name="email" v-model="linkedin.email" class="form-control" placeholder="Email address">
            </div>
        </div>
        <div class="form-group row">
            <label for="linkedin_password" class="col-sm-3 col-form-label">Password</label>
            <div class="col-sm-9">
                <input type="password" id="linkedin_password" name="password" v-model="linkedin.password" class="form-control" placeholder="Password">
            </div>
        </div>
        <div class="form-group row mt-3">
            <label for="linkedin_password" class="col-sm-3 col-form-label">LinkedIn API token</label>
            <div class="col-sm-9">
                <div class="input-group mb-3">
                    <input type="text" id="linkedin_token" v-model="linkedin.token" class="form-control" placeholder="LinkedIn API token" aria-label="LinkedIn API token" aria-describedby="basic-addon2">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit">Get</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>

<script>
    export default {
        data() {
            return {
                errors: [],
                saved: false,
                linkedin: {
                    email: null,
                    password: null,
                    token: null
                }
            };
        },

        methods: {
            onSubmit() {
                this.saved = false;

                axios.post('api/linkedin/store', this.linkedin)
                     .then(({data}) => this.setSuccessMessage(data.data))
                     .catch(({response}) => this.setErrors(response));
            },

            setErrors(response) {
            },

            setSuccessMessage(data) {
                this.reset();
                this.saved = true;
                this.linkedin = {email: data.email, password: data.password, token: data.token};
            },

            reset() {
                this.errors = [];
                // this.linkedin = {email: null, password: null, data: null};
            }
        }
    }
</script>
