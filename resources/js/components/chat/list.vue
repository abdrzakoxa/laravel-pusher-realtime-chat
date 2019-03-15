<template>
    <div>
        <div class="chat_window">
            <div class="top_menu">
                <div class="buttons">
                    <div class="button close"></div>
                    <div class="button minimize"></div>
                    <div class="button maximize"></div>
                </div>
                <div class="title">Your messages</div>
            </div>
            <div class="msg-body left">
                <div v-for="msg in messages">
                    <router-link :to="'chat/'+msg.from_user_id" class="contact">
                        <div class="avatar">
                            <span class="icon-status"></span>
                        </div>
                        <div class="detail-msg">
                            <span class="user-name">{{ msg.from_user.name }}</span>
                        </div>
                        <div class="status-message">
                            <span class="msg-count">{{ msg.total }}</span>
                        </div>
                    </router-link>
                </div>


            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "list",
        data(){
            return {
                messages: {

                },
                currentUser: {}
            }
        },
        methods: {
            /**
             * get the users chating with me
             * */
            getAuthMessages(){
                axios.get('/api/messages').then((e) => {
                    this.messages = e.data.data;
                })
            },

            /**
             * get user details in server for listen to event
             * */
            async setUserDetail(){
                await axios.get('/api/user').then((e) => {
                    this.currentUser = e.data;
                    return true;
                });
            },

        },
        async created() {
            this.getAuthMessages()
            await this.setUserDetail();
        }
    }
</script>

<style scoped>

</style>