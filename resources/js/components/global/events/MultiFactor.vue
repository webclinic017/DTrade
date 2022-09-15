<template>
    <div>
        <el-dialog title="Robinhood Multi-Factor Authentication" :visible.sync="dialogVisible" @close="submit">
            <p>
                {{ message }}
            </p>
            <el-form :model="dialogForm">
                <el-form-item label="MFA Code:">
                    <el-input v-model="dialogForm.robinhoodMFA" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="success" @click="submit" >Submit</el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
        <el-dialog title="Robinhood Authentication Failed" :visible.sync="failureDialogVisible">
            <p>
                {{ failureMessage }}
            </p>
            <span slot="footer" class="dialog-footer">
                <el-button @click="failureDialogVisible = false">Close</el-button>
                <el-button type="primary" @click="retry">Retry Authentication</el-button>
            </span>
        </el-dialog>
    </div>

</template>

<script>
    export default {
        props: {
            'userId': { required: true, type: Number }
        },
        data () {
            return {
                message: '',
                submitted: false,
                dialogVisible: false,
                dialogForm: {
                    robinhoodMFA: null,
                },
                failureMessage: '',
                failureDialogVisible: false,
            };
        },
        methods: {
            displayError (message) {
                this.$notify({
                    title: 'Error',
                    message: message,
                    type: 'error',
                    duration: 2000
                })
            },
            submit () {
                if (!this.submitted && this.dialogForm.robinhoodMFA !== null) {
                    axios.post(`/api/user/${this.userId}/robinhood/mfa`, { code: this.dialogForm.robinhoodMFA })
                        .then(() => {
                            this.submitted = true;
                            this.dialogVisible = false;
                        })
                        .catch((e) => {
                            this.displayError(e.message)
                        });
                }
            },
            retry () {
                axios.post(`/api/user/${this.userId}/robinhood/refreshPortfolio`)
                    .then(() => {
                        this.submitted = false;
                        this.failureDialogVisible = false;
                    })
                    .catch((e) => {
                        this.displayError(e.message)
                    });
            }
        },
        mounted () {
            Echo.private(`user.${ this.userId }`)
                .listen('Robinhood.MultiFactorNecessary', (result) => {
                    this.submitted = false;
                    this.message = result.message;
                    this.dialogVisible = true;
                })
                .listen('Robinhood.MultiFactorFailed', (result) => {
                    this.submitted = false;
                    this.failureMessage = result.message;
                    this.failureDialogVisible = true;
                });
        }
    }
</script>

<style scoped>

</style>
