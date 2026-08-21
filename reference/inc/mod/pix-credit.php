<?php
if (!class_exists('Credit')) {
    class Credit {

        public static function credit_change($user_id,$_credit,$context = array()){

            global $user_current_credit;

            $result = self::change_wallet_asset($user_id, 'ppo_credit', 'credit', $_credit, 0, $context);
            if ($result === false) {
                return false;
            }

            $user_current_credit = $result['before'];

            // 积分钩子
            return apply_filters('ppo_change_credit',(int)$result['after'],$user_id);

        }

        public static function get_user_credit($user_id){

            $credit = get_user_meta($user_id,'ppo_credit',true);
            return $credit ? (int)$credit : 0;

        }

        // 更改用户余额
        public static function cash_change($user_id,$_cash,$context = array()){

            global $user_current_cash;
            $result = self::change_wallet_asset($user_id, 'ppo_balance', 'cash', $_cash, 2, $context);
            if ($result === false) {
                return false;
            }

            $user_current_cash = $result['before'];

            return apply_filters('ppo_change_balance',$result['after'],$user_id);

        }

        public static function get_user_balance($user_id){

            $credit = get_user_meta($user_id,'ppo_balance',true);
            return $credit ? $credit : 0;

        }

        private static function change_wallet_asset($user_id, $meta_key, $asset_type, $change_amount, $scale, $context = array()){
            global $wpdb;

            $user_id = intval($user_id);
            $lock_name = 'ppo_wallet_' . $asset_type . '_' . $user_id;
            if ($user_id <= 0 || !self::acquire_wallet_lock($lock_name)) {
                return false;
            }

            $started_transaction = false;

            try {
                $wpdb->query('START TRANSACTION');
                $started_transaction = true;

                $before = $asset_type === 'credit'
                    ? self::get_user_credit($user_id)
                    : floatval(self::get_user_balance($user_id));

                $after = $scale === 0
                    ? intval($before) + intval($change_amount)
                    : round(floatval($before) + floatval($change_amount), $scale);

                if ($after < 0) {
                    $wpdb->query('ROLLBACK');
                    return false;
                }

                $stored_after = $scale === 0 ? intval($after) : number_format($after, $scale, '.', '');
                $updated = update_user_meta($user_id, $meta_key, $stored_after);
                if ($updated === false) {
                    $wpdb->query('ROLLBACK');
                    return false;
                }

                $logged = self::log_wallet_ledger($user_id, $asset_type, $change_amount, $before, $after, $context);
                if ($logged === false) {
                    $wpdb->query('ROLLBACK');
                    return false;
                }

                $wpdb->query('COMMIT');

                return array(
                    'before' => $before,
                    'after' => $scale === 0 ? intval($after) : $stored_after,
                );
            } catch (\Throwable $e) {
                if ($started_transaction) {
                    $wpdb->query('ROLLBACK');
                }
                return false;
            } finally {
                self::release_wallet_lock($lock_name);
            }
        }

        private static function acquire_wallet_lock($lock_name, $timeout = 5){
            global $wpdb;
            $locked = $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, %d)', $lock_name, intval($timeout)));
            return intval($locked) === 1;
        }

        private static function release_wallet_lock($lock_name){
            global $wpdb;
            $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock_name));
        }

        private static function log_wallet_ledger($user_id, $asset_type, $change_amount, $before_amount, $after_amount, $context = array()){
            global $wpdb;

            self::maybe_create_wallet_ledger_table();

            $table_name = $wpdb->prefix . 'ppo_wallet_ledger';
            $context = is_array($context) ? $context : array();

            return $wpdb->insert($table_name, array(
                'user_id'        => intval($user_id),
                'asset_type'     => sanitize_key($asset_type),
                'change_amount'  => round(floatval($change_amount), 4),
                'before_amount'  => round(floatval($before_amount), 4),
                'after_amount'   => round(floatval($after_amount), 4),
                'change_type'    => isset($context['change_type']) ? sanitize_key($context['change_type']) : 'adjust',
                'order_id'       => isset($context['order_id']) ? sanitize_text_field($context['order_id']) : '',
                'trade_no'       => isset($context['trade_no']) ? sanitize_text_field($context['trade_no']) : '',
                'note'           => isset($context['note']) ? sanitize_text_field($context['note']) : '',
                'created_at'     => current_time('mysql'),
            ));
        }

        private static function maybe_create_wallet_ledger_table(){
            global $wpdb;
            $table_name = $wpdb->prefix . 'ppo_wallet_ledger';
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
                return;
            }

            if (function_exists('ppo_wallet_ledger_install')) {
                ppo_wallet_ledger_install();
            }
        }

    }
}
