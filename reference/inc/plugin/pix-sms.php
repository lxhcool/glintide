<?php
/**
 * 各类短信接口调用
 */

class Sms {
// 发信接口
public static function send_sms($mobile,$verify_code){
    $sms_type = get_op('sms_type');
    
    // 检查配置是否存在
    if (empty($sms_type)) {
        error_log("短信类型未配置");
        return array('error' => '短信服务未配置');
    }

    if ($sms_type !== 'aliyunsms') {
        error_log("短信类型暂未支持：" . $sms_type);
        return array('error' => '当前仅支持阿里云号码认证短信，请在后台保存短信配置');
    }
    
    // 动态调用对应的方法
    $method = $sms_type;
    if (!method_exists('Sms', $method)) {
        error_log("短信方法不存在：" . $method);
        return array('error' => '短信服务配置错误');
    }
    
    $res = self::$method($mobile, $verify_code);
    if(isset($res['error'])){
        return $res;
    }
    
    // 如果没有错误，返回 true 表示成功
    return true;
}


// 阿里云号码认证 验证码短信
/**
 * 发送短信验证码
 * @param string $phoneNumber 目标手机号
 * @return bool 发送成功返回 true，失败返回 false
 */
public static function aliyunsms($phoneNumber,$verify_code) {
    $ali_config = get_op('alisms');
    
    // 检查配置是否存在
    if (empty($ali_config) || !is_array($ali_config)) {
        error_log("阿里云短信配置未设置");
        return false;
    }
    
    // 记录配置信息（脱敏）
    error_log("阿里云号码认证配置 - keyid: " . substr($ali_config['keyid'] ?? '', 0, 8) . "***");
    
    try {
        // 1. 初始化配置
        $config = new \Darabonba\OpenApi\Models\Config();
        $config->accessKeyId = $ali_config['keyid'];
        $config->accessKeySecret = $ali_config['keysecret'];
        $config->regionId = 'cn-hangzhou';
        
        // 2. 创建号码认证服务客户端
        $client = new \AlibabaCloud\SDK\Dypnsapi\V20170525\Dypnsapi($config);
        
        // 3. 构建请求参数
        $sendSmsVerifyCodeRequest = new \AlibabaCloud\SDK\Dypnsapi\V20170525\Models\SendSmsVerifyCodeRequest();
        $sendSmsVerifyCodeRequest->phoneNumber = $phoneNumber;
        $sendSmsVerifyCodeRequest->signName = '速通互联验证码'; // 使用号码认证服务提供的公共签名
        $sendSmsVerifyCodeRequest->templateCode = '100001';    // 使用号码认证服务提供的公共模板 Code
        // 模板变量：code 为验证码
        $sendSmsVerifyCodeRequest->templateParam = json_encode([
            'code' => $verify_code,
            'min' => '5',
        ], JSON_UNESCAPED_UNICODE);
        
        // 4. 发送请求
        $response = $client->sendSmsVerifyCode($sendSmsVerifyCodeRequest);
        
        // 检查响应状态
        if (is_object($response->body) && property_exists($response->body, 'code')) {
            if ($response->body->code === 'OK') {
                return true;
            } else {
                $message = property_exists($response->body, 'message') ? $response->body->message : 'Unknown error';
                error_log('短信发送失败：' . $message);
                return array('error' => '短信发送失败：' . $message);
            }
        } else {
            return array('error' => '短信发送失败：响应格式错误');
        }
    } catch (\Exception $e) {
        error_log('短信发送异常：' . $e->getMessage());
        return array('error' => '短信发送异常：' . $e->getMessage());
    }
}

// 阿里云 sms 接口-------------------------------------------------------------



//end
}
