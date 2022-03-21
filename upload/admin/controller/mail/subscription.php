<?php
namespace Opencart\Admin\Controller\Mail;
class Customer extends \Opencart\System\Engine\Controller {
	public function index(string &$route, array &$args, mixed &$output): void {
		if (isset($args[0])) {
			$customer_id = $args[0];
		} else {
			$customer_id = 0;
		}

		if (isset($args[1])) {
			$description = $args[1];
		} else {
			$description = '';
		}

		if (isset($args[2])) {
			$amount = $args[2];
		} else {
			$amount = 0;
		}

		if (isset($args[3])) {
			$order_id = $args[3];
		} else {
			$order_id = 0;
		}





		$this->load->model('customer/customer');

		$customer_info = $this->model_customer_customer->getCustomer($args[0]);

		if ($customer_info) {
			$this->load->model('setting/store');

			$store_info = $this->model_setting_store->getStore($customer_info['store_id']);

			if ($store_info) {
				$this->load->model('setting/setting');

				$store_logo = html_entity_decode($this->model_setting_setting->getValue('config_logo', $store_info['store_id']), ENT_QUOTES, 'UTF-8');
				$store_name = html_entity_decode($store_info['name'], ENT_QUOTES, 'UTF-8');
				$store_url = $store_info['url'];
			} else {
				$store_logo = html_entity_decode($this->config->get('config_logo'), ENT_QUOTES, 'UTF-8');
				$store_name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
				$store_url = HTTP_CATALOG;
			}

			$this->load->model('localisation/language');

			$language_info = $this->model_localisation_language->getLanguage($customer_info['language_id']);

			if ($language_info) {
				$language_code = $language_info['code'];
			} else {
				$language_code = $this->config->get('config_language');
			}

			// Load the language for any mails using a different country code and prefixing it so it does not pollute the main data pool.
			$this->language->load($language_code, 'mail', $language_code);
			$this->language->load('mail/customer_approve', 'mail', $language_code);

			// Add language vars to the template folder
			$results = $this->language->all('mail');

			foreach ($results as $key => $value) {
				$data[$key] = $value;
			}

			$this->load->model('tool/image');

			if (is_file(DIR_IMAGE . $store_logo)) {
				$data['logo'] = $store_url . 'image/' . $store_logo;
			} else {
				$data['logo'] = '';
			}

			$subject = sprintf($this->language->get('mail_text_subject'), $store_name);

			$data['text_welcome'] = sprintf($this->language->get('mail_text_welcome'), $store_name);

			$data['login'] = $store_url . 'index.php?route=account/login';

			$data['store'] = $store_name;
			$data['store_url'] = $store_url;

			$mail = new \Opencart\System\Library\Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($customer_info['email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender($store_name);
			$mail->setSubject($subject);
			$mail->setHtml($this->load->view('mail/customer_approve', $data));
			$mail->send();
		}
	}

	public function cancel(string &$route, array &$args, mixed &$output): void {


	}
}
