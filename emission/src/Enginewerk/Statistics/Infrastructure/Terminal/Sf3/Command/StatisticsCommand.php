<?php
namespace Enginewerk\Statistics\Infrastructure\Terminal\Sf3\Command;

use ScriptFUSION\Byte\Base;
use ScriptFUSION\Byte\ByteFormatter;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class StatisticsCommand extends ContainerAwareCommand
{
    const COMMAND_NAME = 'emission:statistics';

    /** @var OutputInterface */
    private $output;

    protected function configure()
    {
        $this->setName(static::COMMAND_NAME);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $fileStatReader = $this->getContainer()->get('enginewerk_stat.presentation_doctrine.file_reader');
        $declaredFilesSize = $fileStatReader->getFilesSize();
        $realFilesSize = $fileStatReader->getFilesSizeReal();

        $fileSizeAndCountTable = (new Table($this->output))
            ->setHeaders(['Files number', 'Size declared (bytes)', 'Size on disk (bytes)']);

        $fileSizeAndCountTable->setRow(
            0,
            [
                $fileStatReader->getFilesCount(),
                sprintf(
                    '%s (%d)',
                    (new ByteFormatter())->setBase(Base::DECIMAL)->setPrecision(2)->format($declaredFilesSize),
                    $declaredFilesSize
                ),
                sprintf(
                    '%s (%d)',
                    (new ByteFormatter())->setBase(Base::DECIMAL)->setPrecision(2)->format($realFilesSize),
                    $realFilesSize
                ),
            ]
        );

        $fileSizeAndCountTable->render();

        $fileTypesTable = (new Table($this->output))
            ->setHeaders(['Files number', 'File type']);

        $fileTypesTable->setRows($fileStatReader->getFileTypesCount());
        $fileTypesTable->render();
    }
}
