<?php

namespace App\Services;

class RunServices extends BaseServices
{
    protected function before(): void
    {
        $phpDir = $this->phpDir;

        foreach (self::CONFIG_FILE_NAME as $configFileName) {
            $confFile = $phpDir . DIRECTORY_SEPARATOR . $configFileName;

            if (!$this->fs->exists($confFile)) {
                continue;
            }

            $userConf = \json_decode($this->fs->readFileSync($confFile), true);

            if (\json_last_error() === JSON_ERROR_NONE) {
                $this->conf = \array_merge($this->conf, $userConf);
            }
        }
    }

    protected function execute(): bool
    {
        $invokeHookName = $this->input->getArgument('hookName');

        $gitParams = $this->input->getArgument('gitParams');
        $dryRun         = $this->input->getOption('dry-run');

        if (isset($this->conf['hooks'])) {
            foreach ($this->conf['hooks'] as $configHookName => $shell) {
                if ($configHookName === $invokeHookName) {
                    $this->io->info("husky > {$invokeHookName}");
                    if (\mb_strtoupper(\mb_substr(PHP_OS, 0, 3)) === 'WIN') {
                        $shell = "bash ${shell}";
                    }

                    if (0 !== \count($gitParams)) {
                        $shell .= ' ' . implode(' ', $gitParams);
                    }

                    $returnCode = 1;

                    if (!$dryRun) {
                        \system($shell, $returnCode);
                    }

                    return $returnCode;
                }
            }
        }

        return true;
    }
}
