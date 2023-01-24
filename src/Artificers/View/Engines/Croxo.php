<?php
    namespace Artificers\View\Engines;

    use Artificers\Filesystem\Filesystem;
    use Artificers\Treaties\View\EngineTreaties;
    use Symfony\Component\Process\Exception\ProcessFailedException;
    use Symfony\Component\Process\Process;

    class Croxo implements EngineTreaties {
        private string $node = "node";
        private string $tmpDir = "";

        public function __construct(string $node, string $tmpDir) {
            $this->node = $node;
            $this->tmpDir = $tmpDir;
        }

        public function run(string $script): string {
            $tmpFile = $this->createTempFile();

            file_put_contents($tmpFile, $script);

            $process = new Process([$this->node, $tmpFile]);

            try {
                return $process->mustRun()->getOutput();
            }catch(ProcessFailedException $e) {
                return $e->getMessage();
            }finally {
                unlink($tmpFile);
            }
        }

        protected function createTempFile():string {
            return $this->tmpDir.DIRECTORY_SEPARATOR.md5(time()).".js";
        }

        public function getDispatchHandler(): string {
            return "console.log";
        }

        public function extractCssHandlerMethod(): string {
            return "extractCss";
        }
    }