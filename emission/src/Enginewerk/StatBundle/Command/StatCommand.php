<?php
namespace Enginewerk\StatBundle\Command;

use ScriptFUSION\Byte\Base;
use ScriptFUSION\Byte\ByteFormatter;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class StatCommand extends ContainerAwareCommand
{
    const COMMAND_NAME = 'emission:stat';

    /** @var InputInterface */
    private $input;

    /** @var OutputInterface */
    private $output;

    protected function configure()
    {
        $this->setName(static::COMMAND_NAME);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $fileStatReader = $this->getContainer()->get('enginewerk_stat.presentation_doctrine.file_reader');

        $fileSizeAndCountTable = (new Table($this->output))
            ->setHeaders(['Files number', 'Size (declared)', 'Size (on disk)']);

        $fileSizeAndCountTable->setRow(
            0,
            [
                $fileStatReader->getFilesCount(),
                (new ByteFormatter())->setBase(Base::DECIMAL)->setPrecision(2)->format($fileStatReader->getFilesSize()),
                (new ByteFormatter())->setBase(Base::DECIMAL)->setPrecision(2)->format($fileStatReader->getFilesSizeReal()),
            ]
        );

        $fileSizeAndCountTable->render();

        $fileTypesTable = (new Table($this->output))
            ->setHeaders(['Files number', 'File type']);

        $fileTypesTable->setRows($fileStatReader->getFileTypesCount());
        $fileTypesTable->render();
    }
}
