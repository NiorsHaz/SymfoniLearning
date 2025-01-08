<?php
    namespace App\Form;

    use App\Entity\User;
    use App\Entity\Task;
    use Symfony\Bridge\Doctrine\Form\Type\EntityType;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\SubmitType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class AssignUserType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                ->add('tasks', EntityType::class, [
                    'class' => Task::class,
                    'choices' => $options['tasks'],
                    'choice_label' => 'title',
                    'label' => 'Select Task',
                ])
                ->add('users', EntityType::class, [
                    'class' => User::class,
                    'choices' => $options['users'],
                    'choice_label' => 'username',
                    'label' => 'Select User',
                    'multiple' => true,
                    'expanded' => true,
                ])
                ->add('submit', SubmitType::class, [
                    'label' => 'Assign Users', 
                    'attr' => ['class' => 'btn btn-primary'],
                ]);
        }

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'users' => [],
                'tasks' => [],
            ]);
        }
    }
?>