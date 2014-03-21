<?php
namespace Hris\RecordsBundle\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Tests\Common\Annotations\True;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\QueryBuilder as QueryBuilder;
use FOS\UserBundle\Doctrine;
use Doctrine\ORM\Internal\Hydration\ObjectHydrator  as DoctrineHydrator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Hris\RecordsBundle\Entity\Record;
use Hris\RecordsBundle\Form\RecordType;
use Hris\OrganisationunitBundle\Entity\Organisationunit;
use Doctrine\Common\Collections\ArrayCollection;
use Hris\FormBundle\Entity\Field;
use Hris\FormBundle\Form\FormType;
use Hris\FormBundle\Form\DesignFormType;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use JMS\SecurityExtraBundle\Annotation\Secure;
//use Liuggio\ExcelBundle\LiuggioExcelBundle;
use DateTime;

class NewsletterCommand extends ContainerAwareCommand
{
    protected function configure()
    {

        $this
            ->setName('demo:fill')
            ->setDescription('Sends our daily newsletter to our registered users')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine');

        $formEntity = $em->getRepository('HrisFormBundle:Form')->find('5');

        $field = $em->getRepository('HrisFormBundle:Field')->findAll();



        $fields = $formEntity->getSimpleField();

        $phpExcelObject = $this->getContainer()->get('phpexcel')->createPHPExcelObject('//home/kelvin/Desktop/MINISTRY_MNHSTAFF/MNH_STAFF.xls');
        $objWorksheet = $phpExcelObject->getActiveSheet();
        $i=0;
        $uidarr = array();
        $fieldarr = array();
        foreach ($objWorksheet->getRowIterator() as $row) {
            if($i < 1){
                $cellIterator = $row->getCellIterator();
                $k = 1;
                foreach ($cellIterator as $cell) {
                    $field = $em->getRepository('HrisFormBundle:Field')->findOneByName($cell->getValue());
                    $uidarr[$k] = $field->getUid();
                    $fieldarr[$k] = $field;
                    $k++;
                }

            }
            $i++;
        }

        $dataarry = array();
        $j = 1;
        foreach ($objWorksheet->getRowIterator() as $row) {
            if($j > 1 && $j < 4){
                $cellIterator = $row->getCellIterator();
                $k = 1;
                $instancestring= "";
                //$instance=md5($firstName.$middleName.$surname.$dateOfBirth->format('Y-m-d'));
                foreach ($cellIterator as $cell) {
                    if($k==2 || $k==3 || $k==4){
                        $instancestring.=$cell->getValue();
                    }
                    if($k==5){
                        $instancestring.=date("Y-m-d",$cell->getValue());
                    }

//                    echo $fieldarr[$k]->getInputType()->getName()."\n";
                    if($fieldarr[$k]->getDataType()->getName() == "Date"){
                        $dataarry[$uidarr[$k]] = new \DateTime(date("Y-m-d",$cell->getValue()));
                    }elseif($fieldarr[$k]->getInputType()->getName() == "Select"){
                        //special check for sex
                        if($fieldarr[$k]->getName() == "sex"){
                            foreach($fieldarr[$k]->getFieldOption() as $option){
                                $val = ($cell->getValue()== "M")?"Male":"Female";
                                if($option->getValue() == $val){
                                    $dataarry[$uidarr[$k]] = $option->getUid();
                                }
                            }
                        }elseif($fieldarr[$k]->getName() == "Religion"){

                            foreach($fieldarr[$k]->getFieldOption() as $option){
                                if(strtolower($option->getValue()) == strtolower($cell->getValue())){
                                    $dataarry[$uidarr[$k]] = $option->getUid();
                                }

                            }
                        }else{
                            foreach($fieldarr[$k]->getFieldOption() as $option){
                                if($option->getValue() == $cell->getValue()){
                                    $dataarry[$uidarr[$k]] = $option->getUid();
                                }
                            }
                        }

                    }else{
                        $dataarry[$uidarr[$k]] = $cell->getValue();
                    }

                    $k++;


                }
                $instance=md5($instancestring);
                    //for basic education level
                    $dataarry["5289e93496216"] = "5289e93871f64";

                // for employment_status
                    $dataarry["5289e934a6b16"] = "5289e934f353d";

                //for employer
                       $dataarry["5289e934a59a6"] = "528a0ae3249d2";


                print_r($dataarry);
            }
            $j++;
        }


    }
}