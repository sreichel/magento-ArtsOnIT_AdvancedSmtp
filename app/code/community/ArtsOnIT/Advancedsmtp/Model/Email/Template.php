<?php
/**
 * Magento ArtsOnIt Advanced Smtp
 *
 * @category   ArtsOnIt
 * @package    Mage_AdvanceSmtp
 * @copyright  Copyright (c) 2008 ArtsOn.IT(http://www.ArtsOn.it)
 * @author     Calore Luca Erico (l.calore@ArtsOn.it)
 */
class ArtsOnIT_Advancedsmtp_Model_Email_Template extends Mage_Core_Model_Email_Template
{
    /**
     * @param array|string $email
     * @param array|string $name
     * @param array $variables
     * @return bool
     */
    public function send($email, $name = null, array $variables = array())
    {
        if (!Mage::getStoreConfigFlag('advancedsmtp/settings/enabled')) {
            return parent::send($email, $name, $variables);
        }

        if (!$this->isValidForSend()) {
            return false;
        }

        $emails = array_values((array)$email);
        $names = is_array($name) ? $name : (array)$name;
        $names = array_values($names);
        foreach ($emails as $key => $email) {
            if (!isset($names[$key])) {
                $names[$key] = substr($email, 0, strpos($email, '@'));
            }
        }
        $variables['email'] = reset($emails);
        $variables['name'] = reset($names);

        $mail = $this->getMail();
        foreach ($emails as $key => $email) {
            $mail->addTo($email, '=?utf-8?B?' . base64_encode($names[$key]) . '?=');
        }

        $this->setUseAbsoluteLinks(true);
        $text = $this->getProcessedTemplate($variables);

        if ($this->isPlain()) {
            $mail->setBodyText($text);
        } else {
            $mail->setBodyHTML($text);
        }

        $mail->setSubject($this->getProcessedTemplateSubject($variables));
        $mail->setFrom($this->getSenderEmail(), $this->getSenderName());

        $transport = Mage::helper('advancedsmtp')->getTransport();
        try {
            $mail->send($transport);
            $this->_mail = null;
        } catch (Exception $e) {
            return false;
        }

        return true;
    }
}
