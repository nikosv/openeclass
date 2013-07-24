<?php
/* ========================================================================
 * Open eClass 2.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */

// Send a mail message, with the proper MIME headers and charset tag
// From: address is always the platform administrator, and the
// $from_address specified appears in the Reply-To: header
function send_mail($from, $from_address, $to, $to_address,
                   $subject, $body, $charset, $extra_headers = '')
{
        if (count($to_address) > 1) {
                $to_header = '(undisclosed-recipients)';
                $bcc = 'Bcc: ' . join(', ', $to_address) . "\n";
        } else {
                if (empty($to)) {
                        $to_header = $to_address;
                } else {
                        $to_header = qencode($to, $charset) . " <$to_address>";
                }
                $bcc = '';
        }
	$headers = from($from, $from_address) . $bcc .
		"MIME-Version: 1.0\r\n" .
		"Content-Type: text/plain; charset=$charset\r\n" .
		"Content-Transfer-Encoding: 8bit" .
                reply_to($from, $from_address);
	if ($extra_headers) {
		$headers .= "\r\n" . preg_replace('/\n+/', "\n", $extra_headers);
	}

	return @mail($to_header, qencode($subject, $charset),
               $body, $headers);
}


// Send a Multipart/Alternative message, with the proper MIME headers
// and charset tag, with a plain text and an HTML part
// From: address is always the platform administrator, and the
// $from_address specified appears in the Reply-To: header
function send_mail_multipart($from, $from_address, $to, $to_address,
                   $subject, $body_plain, $body_html, $charset)
{
        global $emailAnnounce;

        if (count($to_address) > 1) {
                if (isset($emailAnnounce)) {
                        if (empty($to)) {
                                $to_header = $emailAnnounce;
                        } else {
                                $to_header = $to . " <$emailAnnounce>";
                        }
                } else {
                        if (empty($to)) {
                                $to_header = '(undisclosed recipients)';
                        } else {
                                $to_header = "($to)";
                        }
                }
                $bcc = 'Bcc: ' . join(', ', $to_address) . "\r\n";
        } else {
                if (empty($to)) {
                     if (is_array($to_address)) {
                        $to_header = $to_address[0];
                     } else {
                        $to_header = $to_address;
                     }
                } else {
                     if (is_array($to_address)) {
                        $to_header = qencode($to, $charset) . " <{$to_address[0]}>";
                     } else {
                        $to_header = qencode($to, $charset) . " <$to_address>";
                     }
                }
                $bcc = '';
        }
        $separator = uniqid('==eClass-Multipart_Boundary_0_', true) . '_' .
                     md5(time());
	$headers = from($from, $from_address) . $bcc .
		   "MIME-Version: 1.0\r\n" .
                   "Content-Type: multipart/alternative;" .
                   "\r\n\tboundary=\"$separator\"" .
                   reply_to($from, $from_address);

	$body = "This is a multi-part message in MIME format.\n\n" .
		"--$separator\r\n" .
		"Content-Type: text/plain; charset=$charset\n" .
		"Content-Transfer-Encoding: 8bit\n\n$body_plain\n\n" .
		"--$separator\r\n" .
		"Content-Type: text/html; charset=$charset\n" .
		"Content-Transfer-Encoding: 8bit\n\n" .
		"<html><head><meta http-equiv='Content-Type' " .
		"content='text/html; charset=\"$charset\"'>" .
		"<title>message</title></head><body>\n" .
		"$body_html\n</body></html>\n\n" .
		"--$separator--\r\n";

	return @mail($to_header, qencode($subject, $charset),
               $body, $headers);
}


// Determine the correct From: header
function from($from, $from_address)
{     
        global $langVia, $siteName, $emailAdministrator, $charset;

        if (empty($from_address) or !get_config('email_from')) {                
                return "From: " . qencode($siteName, $charset) .
                       " <$emailAdministrator>\r\n";
        } else {                
		return "From: " .
                       qencode("$from ($langVia: $siteName)", $charset) .
                       " <$from_address>\r\n";
        }
}


// Determine the correct Reply-To: header if needed
function reply_to($from, $from_address)
{
        global $siteName, $emailAdministrator, $emailAnnounce, $charset;

        if (!get_config('email_from') and $emailAdministrator <> $from_address) {
                if (empty($from)) {
                        return "\r\nReply-To: $from_address";
                } else {
                        return "\r\nReply-To: " .
                                    qencode($from, $charset) .
                                    " <$from_address>";
                }
        } else {
                return '';
        }
}


// Encode a mail header line with according to MIME / RFC 2047
function qencode($header, $charset)
{
	// If header contains no chars > 128, return it without encoding
	if (!preg_match('/[\200-\377]/', $header)) {
		return $header;
	} else {
                mb_internal_encoding('UTF-8');
	        return mb_encode_mimeheader($header, $charset);
        }
}
