<?php
    namespace App\Form;

    use App\Entity\Project;
    use App\Entity\Task;
    use Symfony\Bridge\Doctrine\Form\Type\EntityType;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\SubmitType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class AssignTaskType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                ->add('project', EntityType::class, [
                    'class' => Project::class,
                    'choices' => $options['projects'],
                    'choice_label' => 'name',
                    'label' => 'Select Project',
                ])
                ->add('tasks', EntityType::class, [
                    'class' => Task::class,
                    'choices' => $options['tasks'],
                    'choice_label' => 'title',
                    'label' => 'Select Tasks',
                    'multiple' => true,
                    'expanded' => true,
                ])
                ->add('submit', SubmitType::class, [
                    'label' => 'Assign Tasks',
                    'attr' => ['class' => 'btn btn-primary'],
                ]);
        }

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'projects' => [],
                'tasks' => [],
            ]);
        }
    }
?>