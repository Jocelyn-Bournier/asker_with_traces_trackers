<?php

namespace CRT\ToolBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use CRT\ToolBundle\Definitions\Definitions;

class ExpiredCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('crttool:expired')
            ->setDescription('Change the status for old request staying in waiting status')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ema = $this->getContainer()->get('doctrine')->getManager();
        $validity = $this->getContainer()->getParameter('requestValidity');
        $def = new Definitions();
        $did = 0;

        $now = new \DateTime();
        $expireDate = $now->sub(new \DateInterval($validity));
        $status = $ema
            ->getRepository('CRTToolBundle:RequestStatus')
            ->findOneByLabel($def->getWaiting())
        ;
        $olds = $ema
            ->getRepository('CRTToolBundle:Request')
            ->findOlds($status, $expireDate)
        ;
        $expired = $ema
            ->getRepository('CRTToolBundle:RequestStatus')
            ->findOneByLabel($def->getNoAnswer())
        ;
        foreach($olds as $old)
        {
            $old->setStatus($expired);
            $did = 1;
        }
        if ($did == 1){
            $ema->flush();
        }
       // $output->writeln($text);
    }
}

