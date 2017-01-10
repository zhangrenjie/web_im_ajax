<?php
$senderUid = (int)$_GET['from'];
$reciverUid = (int)$_GET['to'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>欢迎聊天</title>
    <style type="text/css">
        #message-list {
            margin: 0 auto;
            border: solid 1px #666;
            width: 500px;
            height: 400px;
            overflow: auto;
        }

        #message-send {
            margin: 15px auto;
            padding-left: 10px;
            padding-top: 10px;
            /*border: solid 1px #666;*/
            width: 500px;
            height: 60px;
            background: #fff;
            overflow: hidden;
        }

        #message-box {
            width: 380px;
            height: 40px;
        }
    </style>
    <script type="text/javascript" src="jquery.min.js"></script>
    <script type="text/javascript">
        var reciver_uid = <?php echo $senderUid;?>;
        var sender_uid = <?php echo $reciverUid;?>;
        var url = './GetMessage.php';
        $(function () {
            get_message_reply(url, reciver_uid, sender_uid, 'get_message', '');
        });


        //获取消息并应答
        //get_get_message_reply()
        //param request_type  请求类型 详解：
        //      get_message   获取信息
        //      comfrim_read  确认已经读取了信息
        function get_message_reply(url, reciver_uid, sender_uid, request_type, send_data) {
            var setting = {
                url: url,
                data: {
                    'request_type': request_type,
                    'reciver_uid': reciver_uid,
                    'sender_uid': sender_uid,
                    'send_data': send_data,
                },
                type: 'post',
                dataType: 'json',
                success: function (response) {
                    if (response.status == 1) {
                        if (response.response_type == 'is_read') {
                            //将消息写入到消息盒子
                            var messages = response.info;
                            var message_str = '';
                            var id_arr = new Array();
                            for (var i in messages) {
                                id_arr.push(messages[i]['id']);
                                message_str += '<li>' + messages[i]['sender_uid'] + '在' + messages[i]['send_time'] + '的时候对您说：' + messages[i]['content'] + '</li>';
                            }
                            $('#message-list').append(message_str);
                            get_message_reply(url, reciver_uid, sender_uid, 'comfrim_read', id_arr);

                        } else if (response.response_type == 'is_connecting') {
                            get_message_reply(url, reciver_uid, sender_uid, 'get_message', '');
                        }
                    }
                }
            };
            $.ajax(setting);
        }
    </script>
</head>
<body>
<div id="message-list">

</div>

<div id="message-send">
    <input type="textarea" id="message-box"/>
    <input type="button" id="submit-message" value="发送消息">
</div>

<script type="text/javascript">
    //-------------发送消息---------
    $(function () {
        var reciver_uid = <?php echo $reciverUid;?>;
        var sender_uid = <?php echo $senderUid;?>;
        $('#submit-message').on('click', function () {
            var message_content = $('#message-box').val();
            if (message_content != '') {
                $(this).attr('disabled', 'disabled');
                var send_url = './SendMessage.php';
                var send_data = {
                    'message': message_content,
                    'reciver_uid': reciver_uid,
                    'sender_uid': sender_uid,
                };
                $.post(send_url, send_data, function (response) {
                    if (response.status == 1) {
                        $('#message-box').val('');
                        $('#submit-message').removeAttr('disabled');
                        var send_message_str = '<li style="text-align: right;padding-right: 10px;">';
                        send_message_str += '您对' + send_data.reciver_uid + '说：' + send_data.message;
                        send_message_str += '</li>';
                        $('#message-list').append(send_message_str);
                    } else {
                        console.log('发送失败!！');
                    }
                }, 'json');

            }

        });

    });
</script>
</body>
</html>
