<?php

namespace Cesargb\Log\Processors;

class RotativeProcessor extends AbstractProcessor
{
	private $maxFiles = 366;
	private $destDir = null;

	/**
	 * Log files are rotated count times before being removed
	 *
	 * @param int $count
	 * @return self
	 */
	public function files(int $count): self
	{
		$this->maxFiles = $count;

		return $this;
	}

	public function handler($file): ?string
	{
		$nextFile = "{$this->getDestFile()}.1";

		$this->rotate();

		rename($file, $nextFile);

		return $this->processed($nextFile);
	}

	/**
	 * @return string
	 */
	public function getDestFile(): string
	{
		if (null !== $this->getDestDir()) {
			return dirname($this->fileOriginal).'/'.$this->getDestDir().'/'.basename($this->fileOriginal);
		} else {
			return $this->fileOriginal;
		}
	}

	private function rotate(int $number = 1): string
	{
		$destFile = $this->getDestFile();
		$file = "{$destFile}.{$number}{$this->suffix}";

		if (!file_exists($file)) {
			return "{$destFile}.{$number}{$this->suffix}";
		}

		if ($this->maxFiles > 0 && $number >= $this->maxFiles ) {
			if (file_exists($file)) {
				unlink($file);
			}

			return "{$destFile}.{$number}{$this->suffix}";
		}

		$nextFile = $this->rotate($number + 1);

		rename($file, $nextFile);

		return "{$destFile}.{$number}{$this->suffix}";
	}

	public function getDestDir()
	{
		return $this->destDir;
	}

	public function setDestDir($destDir)
	{
		$this->destDir = $destDir;
		return $this;
	}

	private function getnumber(string $file): ?int
	{
		$fileName = basename($file);
		$fileOriginaleName = basename($this->fileOriginal);

		preg_match("/{$fileOriginaleName}.([0-9]+){$this->suffix}/", $fileName, $output);

		return $output[1] ?? null;
	}
}
