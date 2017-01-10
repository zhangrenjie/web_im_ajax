<?php
/**
 * Created by PhpStorm.
 * User: zhangrenjie
 * Date: 2017/1/2
 * Time: 下午9:01
 */

set_time_limit(0);
$maxInvalidCount = 30;
$link = mysqli_connect(
    '127.0.0.1',  /* The host to connect to 连接MySQL地址 */
    'root',      /* The user to connect as 连接MySQL用户名 */
    '',         /* The password to use 连接MySQL密码 */
    'web_im');    /* The default database to query 连接数据库名称*/

if (!$link) {
    printf("Can't connect to MySQL Server. Errorcode: %s ", mysqli_connect_error());
    exit;
}

//只能用函数来判断是否连接成功
if (mysqli_connect_errno()) {
    echo mysqli_connect_error();
}

$requestType = $_POST['request_type'];
switch ($requestType) {
    case 'get_message'://客户端请求读取消息
        break;
    case 'comfrim_read'://客户端确认已经读取了信息,服务端需要更新读取状态
        $idsArr = $_POST['send_data'];
        $ids = implode(',', $idsArr);
        $sql = "update message set status = 2 where id in ({$ids})";
        mysqli_query($link, $sql);
        break;
    default:
        break;
}

$sql = "select * from message where reciver_uid='{$_POST['reciver_uid']}' and sender_uid='{$_POST['sender_uid']}' and status='1'";

$i = 0;
while (true) {
    //读取数据
    $result = mysqli_query($link, $sql);
    if ($result) {
        $returnArr = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $row['send_time'] = date('Y-m-d H:i:s', $row['create_time']);
            $returnArr[] = $row;
        }
        if (!empty($returnArr)) {
            //返回结果
            $data = [
                'status' => 1,
                'response_type' => 'is_read',
                'info' => $returnArr,
            ];
            echo json_encode($data);
            mysqli_close($link);
            exit();
        }
    }
    $i++;
    //需要给客户端发送确认信息是否还在连接服务器,客户端无回应则整个过程结束
    if ($i == $maxInvalidCount) {
        $data = [
            'status' => 1,
            'response_type' => 'is_connecting',
            'info' => '',
        ];

        echo json_encode($data);
        mysqli_close($link);
        exit();
    }

    file_put_contents('./test.log', date('Y-m-d H:i:s') . "已经执行了{$i}次" . "\r\n", FILE_APPEND);
    sleep(1);
}
