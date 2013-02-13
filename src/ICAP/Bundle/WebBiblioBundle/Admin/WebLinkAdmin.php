<?php
namespace ICAP\Bundle\WebBiblioBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class WebLinkAdmin extends Admin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('url')
            ->add('username', 'email')
            ->add('published', 'checkbox', array('required' => false))
            ->add('tags', 'sonata_type_model', 
            array(
                'compound' => true,
                'expanded' => true, 
                'by_reference' => true,
                'multiple' => true,
                'cascade_validation' => true
            ), 
            array(
                'edit' => 'inline',
                'inline' => 'table',
                'sortable'  => 'name'
            ))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('username')
            ->add('url')
            ->add('tags', null, array('filter_field_options' => array('expanded' => true, 'multiple' => true)))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('url')
            ->add('tags')
            ->add('username')
        ;
    }
}  