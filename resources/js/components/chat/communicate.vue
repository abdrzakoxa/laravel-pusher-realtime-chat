<template>
<div>
    <div class="chat_window">
        <div class="top_menu">
            <div class="buttons">
                <div class="button close"></div>
                <div class="button minimize"></div>
                <div class="button maximize"></div>
            </div>
            <div class="title">Chat</div>
        </div>
        <ul class="messages">
            <li v-for="message in messages" class="message" :class="message.message_side" >
                <div class="avatar"></div>
                <div class="text_wrapper">
                    <div class="text">{{ message.text }}</div>
                </div>
            </li>
            <li v-if="is_typing" class="message left" >
                <div class="avatar"></div>
                <div class="text_wrapper">
                    <div class="text">typing...</div>
                </div>
            </li>
        </ul>
        <div class="bottom_wrapper clearfix">
            <div class="message_input_wrapper">
                <input class="message_input" v-model="message_input" @keyup.enter="onSend" placeholder="Type your message here..." />
            </div>
            <div class="send_message" @click="onSend">
                <div class="icon"></div>
                <div class="text">Send</div>
            </div>
        </div>
    </div>
</div>
</template>

<script>
    export default {
        name: "communicate",
        data() {
            return {
                message_input: '',
                toUserId: this.$route.params.id,
                messages: [],
                messages_got_server: [],
                currentUser:{},
                is_typing: false
            }
        },
        watch: {
          message_input() {
              window.Echo.private('message.'+ this.toUserId +'.'+this.currentUser.id)
                  .whisper('typing', {
                      name: this.message_input,
                  });
          }
        },
        methods: {
            // onCreated(){
            //     (function () {
            //         var Message;
            //         Message = function (arg) {
            //             this.text = arg.text, this.message_side = arg.message_side;
            //             this.draw = function (_this) {
            //                 return function () {
            //                     var $message;
            //                     $message = $($('.message_template').clone().html());
            //                     $message.addClass(_this.message_side).find('.text').html(_this.text);
            //                     console.log(_this.message_side);
            //                     $('.messages').append($message);
            //                     return setTimeout(function () {
            //                         return $message.addClass('appeared');
            //                     }, 0);
            //                 };
            //             }(this);
            //             return this;
            //         };
            //         $(function () {
            //             var getMessageText, toggleMessage;
            //             getMessageText = function () {
            //                 var $message_input;
            //                 $message_input = $('.message_input');
            //                 return $message_input.val();
            //             };
            //             toggleMessage = function (text, message_side='right') {
            //                 var $messages, message;
            //                 if (text.trim() === '') {
            //                     return;
            //                 }
            //                 $('.message_input').val('');
            //                 $messages = $('.messages');
            //                 message_side = message_side === 'left' ? 'right' : 'left';
            //                 message = new Message({
            //                     text: text,
            //                     message_side: message_side
            //                 });
            //                 message.draw();
            //                 return $messages.animate({ scrollTop: $messages.prop('scrollHeight') }, 300);
            //             };
            //             $('.send_message').click(function (e) {
            //                 return toggleMessage(getMessageText());
            //             });
            //             $('.message_input').keyup(function (e) {
            //                 if (e.which === 13) {
            //                     return toggleMessage(getMessageText());
            //                 }
            //             });
            //
            //             toggleMessage('Hello Philip! :)');
            //             setTimeout(function () {
            //                 return toggleMessage('Hi Sandy! How are you?','left');
            //             }, 1000);
            //             return setTimeout(function () {
            //                 return toggleMessage('I\'m fine, thank you!');
            //             }, 2000);
            //         });
            //     }.call(this));
            // },

            /**
             * draw the message
             * */
            draw (message) {
                const $message = {
                    text: message.text,
                    message_side: message.message_side
                };

                this.messages.push($message);

            },

            /**
             * create the message and draw
             * */
            toggleMessage (text, message_side='right') {
                if (text.trim() === '') {
                    return;
                }

                if (message_side === 'right') {
                    this.saveMessage(text);
                }

                this.message_input = '';
                this.draw({
                    text: text,
                    message_side: message_side
                });
                this.scrollMessages();
            },

            /**
             * scroll Animation
             * */
            scrollMessages(){
                const $msgs = $('.messages');
                $msgs.animate({ scrollTop: $msgs.prop('scrollHeight') }, 300);
            },

            /**
             * on click send button and click enter keyboard
             * */
            onSend(){
                return this.toggleMessage(this.message_input);
            },

            /**
            * run the app
            * */
            async run(){
                await axios.post('/api/message/'+ this.toUserId).then((e) => {
                    this.messages_got_server = e.data.data;
                    this.prepareMessages();
                    return true;
                });
            },

            /**
             * Prepare messages
             * */
            prepareMessages(){
                if (this.messages_got_server === []) return;
                var msg;
                this.messages_got_server.forEach((el) => {
                    msg = {
                        text: el.message,
                        message_side: this.toUserId == el.to_user_id ? 'right' : 'left'
                    }
                    this.messages.push(msg);
                    msg = '';
                })
            },

            /**
             * save Message in server
             * */
            saveMessage(message) {
                const $data = {
                    message: message,
                    to_user_id: this.toUserId
                };
                axios.post('/api/message/send',$data).then((e) => {
                    if (e.data.status !== 'success') {
                        alert('You have same error')
                    }
                });
                return true;
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

            /**
             * listen to event pusher
             * */
            listenEvent(){
                window.Echo.private('message.'+ this.toUserId +'.'+this.currentUser.id)
                    .listen('.listen-message', (e) => {
                        const msg = {
                            text: e.message.message,
                            message_side: 'left'
                        };
                        axios.post('/api/read/message/'+e.message.id);
                        this.is_typing = false;
                        this.messages.push(msg);
                        this.scrollMessages();
                    });

                window.Echo.private('message.'+ this.currentUser.id +'.'+this.toUserId)
                    .listenForWhisper('typing',(e) => {
                        if (e.name !== '') {
                            this.is_typing = true;
                            this.scrollMessages();
                        }else {
                            this.is_typing = false;
                        }
                    })
            },

            /**
             * send requist for read message
             * */
            readMessages() {
                axios.post('/api/read/messages/'+this.toUserId);
            }

        },
        async created() {
            await $(document).ready();
            await this.run();
            await this.setUserDetail();
            this.listenEvent();
            this.readMessages();
            this.scrollMessages();
        }
    }
</script>
