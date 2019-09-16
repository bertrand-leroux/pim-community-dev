<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Helps to query product models
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QueryHelpProductModelCommand extends ContainerAwareCommand
{
    /** @var DumperInterface */
    private $fieldDumper;

    /** @var DumperInterface */
    private $attributeDumper;

    public function __construct(
        DumperInterface $fieldDumper,
        DumperInterface $attributeDumper
    ) {
        parent::__construct();

        $this->fieldDumper = $fieldDumper;
        $this->attributeDumper = $attributeDumper;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:product-model:query-help')
            ->setDescription('Display useable product model query filters');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->fieldDumper->dump($output);
        $this->attributeDumper->dump($output);
    }
}
