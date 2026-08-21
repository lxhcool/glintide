<?php
/**
 * Pix主题 - 兼容层（替代原加密模块 pix-table / pix-auth）
 *
 * 原加密模块（pix-admin / pix-table / pix-order / pix-auth / pix-vip / pix-card /
 * pix-pay / pix-wallet / pix-down / pix-integrity / pix-admin-data）依赖 XLOAD 扩展，
 * 本环境已全部移除。订单/支付/VIP/卡密/下载功能随之停用。
 *
 * 本文件提供剩余明文代码仍需要的：
 *   - pix_is_authorized()：授权校验，直接视为已授权（授权系统已移除）
 *   - 私信表 / 钱包流水表安装器：原在加密的 pix-table.php 中，这里按实际查询
 *     结构重建，保证私信、签到/任务积分等功能正常落库
 *
 * @package pix
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Cannot access directly.
}

// ---- 授权校验：直接视为已授权 ----
if ( ! function_exists( 'pix_is_authorized' ) ) {
	function pix_is_authorized() {
		return true;
	}
}

// ---- 私信表安装器 ----
if ( ! function_exists( 'ppo_create_private_messages_table' ) ) {
	function ppo_create_private_messages_table() {
		global $wpdb;
		$table           = $wpdb->prefix . 'ppo_private_messages';
		$charset_collate = $wpdb->get_charset_collate();

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) === $table ) {
			return true;
		}

		$sql = "CREATE TABLE {$table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			conversation_key VARCHAR(64) NOT NULL DEFAULT '',
			sender_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			receiver_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			message LONGTEXT NOT NULL,
			send_time DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			is_read TINYINT(1) NOT NULL DEFAULT 0,
			deleted_by_sender TINYINT(1) NOT NULL DEFAULT 0,
			deleted_by_receiver TINYINT(1) NOT NULL DEFAULT 0,
			PRIMARY KEY  (id),
			KEY conversation_key (conversation_key),
			KEY sender_id (sender_id),
			KEY receiver_id (receiver_id),
			KEY is_read (is_read)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		return $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) === $table;
	}
}

// ---- 钱包流水表安装器（余额/积分变动日志） ----
if ( ! function_exists( 'ppo_wallet_ledger_install' ) ) {
	function ppo_wallet_ledger_install() {
		global $wpdb;
		$table           = $wpdb->prefix . 'ppo_wallet_ledger';
		$charset_collate = $wpdb->get_charset_collate();

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) === $table ) {
			return true;
		}

		$sql = "CREATE TABLE {$table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			asset_type VARCHAR(20) NOT NULL DEFAULT 'credit',
			change_amount DECIMAL(14,4) NOT NULL DEFAULT 0,
			before_amount DECIMAL(14,4) NOT NULL DEFAULT 0,
			after_amount DECIMAL(14,4) NOT NULL DEFAULT 0,
			change_type VARCHAR(40) NOT NULL DEFAULT 'adjust',
			order_id VARCHAR(64) NOT NULL DEFAULT '',
			trade_no VARCHAR(64) NOT NULL DEFAULT '',
			note TEXT NULL,
			created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY asset_type (asset_type),
			KEY change_type (change_type),
			KEY created_at (created_at)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		return $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) === $table;
	}
}
