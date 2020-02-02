<?php

namespace com\carlgo11\tempfiles;

use DateTime;
use Exception;

class FileStorage
{
	/**
	 * Get File deletion time
	 *
	 * @param string $id ID of the file.
	 * @return false|string
	 */
	public function getFileDeletionTime($id) {
		global $conf;
		$file = fopen($conf['file-path'] . $id, "r");
		$json = json_decode($file);
		return base64_decode($json['time']);
	}

	public function deleteFile($id) {
		global $conf;
		return unlink($conf['file-path'] . $id);
	}

	public function getFiles() {
		global $conf;
		$files = array_diff(scandir($conf['file-path']), ['.', '..']);

		foreach ($files as $file) {
			$file = fopen($file, "r");
			$json = json_decode($file);
		}

		if (!isNull(NULL)) {
			$json['time'];
		}
	}

	/**
	 * Save file to storage.
	 *
	 * @param File $file
	 * @param string $password
	 * @throws Exception
	 */
	public function saveFile(File $file, string $password) {
		global $conf;
		$content = [];
		$newFile = fopen($conf['file-path'] . $file->getID(), "w");

		$fileContent = Encryption::encryptFileContent($file->getContent(), $password);
		$fileMetadata = Encryption::encryptFileDetails($file->getMetaData(), $file->getDeletionPassword(), 0, $file->getMaxViews(), $password);
		$iv = [base64_encode($fileContent['iv']), base64_encode($fileContent['tag']), base64_encode($fileMetadata['iv']), base64_encode($fileMetadata['tag'])];
		$date = new DateTime('+1 day');
		$time = $date->getTimestamp();

		$content['time'] = $time;
		$content['metadata'] = $fileMetadata['data'];
		$content['iv'] = base64_encode(implode(' ', $iv));
		$content['content'] = $fileContent['data'];

		$txt = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		fwrite($newFile, $txt);
		fclose($newFile);
	}

	/**
	 * Get File from storage
	 *
	 * @param string $id ID of the file.
	 * @param string $password Password of the file.
	 * @return False|File Returns the saved file as a File object.
	 */
	public function getFile(string $id, string $password) {
		global $conf;
		$plaintext = file_get_contents($conf['file-path'] . $id);
		$json = json_decode($plaintext, TRUE);
		$file = new File(NULL);
		$iv = base64_decode($json['iv']);
		$iv_array = explode(' ', $iv);
		$file->setIV([
			'content_iv' => base64_decode($iv_array[0]),
			'content_tag' => base64_decode($iv_array[1]),
			'metadata_iv' => base64_decode($iv_array[2]),
			'metadata_tag' => base64_decode($iv_array[3])
		]);

		$content = Encryption::decrypt($json['content'], $password, $file->getIV('content_iv'), $file->getIV('content_tag'));
		$metadata_string = Encryption::decrypt(base64_decode($json['metadata']), $password, $file->getIV('metadata_iv'), $file->getIV('metadata_tag'));

		if ($content === FALSE) return FALSE;
		if ($metadata_string === FALSE) return FALSE;

		$metadata_array = explode(' ', $metadata_string);
		$metadata = ['name' => $metadata_array[0], 'size' => $metadata_array[1], 'type' => $metadata_array[2]];
		$views_array = explode(' ', base64_decode($metadata_array[4]));

		$file->setContent($content);
		$file->setMetaData($metadata);
		$file->setCurrentViews((int)$views_array[0]);
		$file->setMaxViews((int)$views_array[1]);
		$file->setDeletionPassword(base64_decode($metadata_array[3]));

		return $file;
	}
}
