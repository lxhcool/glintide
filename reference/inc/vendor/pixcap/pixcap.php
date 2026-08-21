<?php
/**
 * Pixcap - 无感人机验证 (Proof-of-Work)
 *
 * 服务端核心类：
 *  - createChallenge()  生成工作量证明挑战
 *  - verifySolution()   校验客户端提交的解
 *
 * 协议（与 public/js/pixcap.js 保持一致）：
 *  - 挑战字段：challenge / algorithm / cost / keyLength / expires / signature
 *  - 签名：HMAC-SHA256(hmacKey, challenge|algorithm|cost|keyLength|expires)
 *  - 解：客户端寻找 nonce，使 hash('sha256', challenge.nonce) % cost === 0
 */

namespace Pixcap;

if (!class_exists('Pixcap\\Pixcap')) {

class Pixcap {

    /** @var string 挑战签名密钥 */
    private $hmacKey;

    /** @var string 备用密钥（保留字段） */
    private $fastKey;

    /** @var float 挑战有效期（秒） */
    private $ttl;

    public function __construct($hmacKey, $fastKey = '', $ttl = 15.0) {
        $this->hmacKey = (string) $hmacKey;
        $this->fastKey = (string) $fastKey;
        $this->ttl     = max(1.0, (float) $ttl);
    }

    /**
     * 生成一个挑战。
     *
     * @param array $params algorithm / cost / keyLength / expires
     * @return array
     */
    public function createChallenge(array $params = array()) {
        $algorithm = isset($params['algorithm']) ? (string) $params['algorithm'] : 'PBKDF2/SHA-256';
        $cost      = isset($params['cost'])      ? max(1000, min(1000000, (int) $params['cost'])) : 50000;
        $keyLength = isset($params['keyLength']) ? max(16, (int) $params['keyLength']) : 32;
        $expires   = isset($params['expires'])   ? (int) $params['expires'] : (time() + (int) $this->ttl);

        $challenge = bin2hex(random_bytes(16));
        $signature = $this->sign($challenge, $algorithm, $cost, $keyLength, $expires);

        return array(
            'challenge' => $challenge,
            'algorithm' => $algorithm,
            'cost'      => $cost,
            'keyLength' => $keyLength,
            'expires'   => $expires,
            'signature' => $signature,
            'type'      => 'proof-of-work',
        );
    }

    /**
     * 校验客户端提交的解。
     *
     * @param array $opts ['payload' => array]
     * @return array verified / expired / invalidSignature / invalidSolution
     */
    public function verifySolution(array $opts = array()) {
        $payload = isset($opts['payload']) ? $opts['payload'] : array();
        if (!is_array($payload)) {
            return $this->fail('invalidSolution');
        }

        $challenge = isset($payload['challenge']) ? (string) $payload['challenge'] : '';
        $algorithm = isset($payload['algorithm']) ? (string) $payload['algorithm'] : '';
        $cost      = isset($payload['cost'])      ? (int) $payload['cost'] : 0;
        $keyLength = isset($payload['keyLength']) ? (int) $payload['keyLength'] : 0;
        $expires   = isset($payload['expires'])   ? (int) $payload['expires'] : 0;
        $signature = isset($payload['signature']) ? (string) $payload['signature'] : '';
        $nonce     = isset($payload['nonce'])     ? (string) $payload['nonce'] : '';

        if ($challenge === '' || $cost < 1000 || $keyLength < 16 || $nonce === '') {
            return $this->fail('invalidSolution');
        }

        if ($expires > 0 && time() > $expires) {
            return array('verified' => false, 'expired' => true);
        }

        $expected = $this->sign($challenge, $algorithm, $cost, $keyLength, $expires);
        if (!hash_equals($expected, $signature)) {
            return array('verified' => false, 'invalidSignature' => true);
        }

        if (!$this->isValidProof($challenge, $nonce, $cost)) {
            return $this->fail('invalidSolution');
        }

        return array('verified' => true);
    }

    /**
     * 对挑战字段签名。
     */
    private function sign($challenge, $algorithm, $cost, $keyLength, $expires) {
        return hash_hmac(
            'sha256',
            $challenge . '|' . $algorithm . '|' . $cost . '|' . $keyLength . '|' . $expires,
            $this->hmacKey
        );
    }

    /**
     * 校验工作量证明：hash('sha256', challenge.nonce) % cost === 0
     */
    private function isValidProof($challenge, $nonce, $cost) {
        if (!preg_match('/^[a-f0-9]{1,64}$/i', $nonce)) {
            return false;
        }
        $digest = hash('sha256', $challenge . $nonce);
        $value = hexdec(substr($digest, 0, 6));
        return ($value % $cost) === 0;
    }

    private function fail($reason) {
        return array('verified' => false, 'invalidSolution' => true, 'reason' => $reason);
    }
}

}
