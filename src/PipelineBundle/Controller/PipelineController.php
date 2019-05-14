<?php

namespace PipelineBundle\Controller;

use OrganisationBundle\Service\OrganisationService;
use PipelineBundle\Service\DealService;
use PipelineBundle\Service\PipelineService;
use PipelineBundle\Service\StageService;
use PipelineBundle\Transformer\DealStatTransformer;
use PipelineBundle\Transformer\OrganisationTransformer;
use PipelineBundle\Transformer\StageStatTransformer;
use Salesinteract\Exception\ValidationException;
use Salesinteract\Rbac\Enum\Role;
use SalesinteractBundle\Service\SessionService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use UserBundle\Service\UserService;

class PipelineController extends Controller
{
    /**
     * @var PipelineService
     */
    private $pipelineService;

    /**
     * @var DealService
     */
    private $dealService;

    /**
     * @var StageService
     */
    private $stageService;

    /**
     * @var OrganisationService
     */
    private $organisationService;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * PipelineController constructor.
     * @param PipelineService $pipelineService
     * @param DealService $dealService
     * @param StageService $stageService
     * @param OrganisationService $organisationService
     * @param UserService $userService
     */
    public function __construct(
        PipelineService $pipelineService,
        DealService $dealService,
        StageService $stageService,
        OrganisationService $organisationService,
        UserService $userService
    ) {
        $this->pipelineService = $pipelineService;
        $this->dealService = $dealService;
        $this->stageService = $stageService;
        $this->organisationService = $organisationService;
        $this->userService = $userService;
    }

    /**
     * @throws AccessDeniedHttpException
     *
     * @return Response
     */
    public function settingsAction(Request $request)
    {
        $organisation = $request->get('activeOrganisation');
        $user = $this->userService->getActiveUser();

        if (!$user->hasRole(Role::ADMIN)) {
            throw new AccessDeniedHttpException();
        }

        return $this->render(
            'PipelineBundle:Pipeline:settings.html.twig',
            ['pipelines' => $this->pipelineService->find(['archived' => false, 'organisationId' => $organisation->getId()])]
        );
    }

    /**
     * @throws AccessDeniedHttpException
     *
     * @return Response
     */
    public function createAction()
    {
        $user = $this->userService->getActiveUser();

        if (!$user->hasRole(Role::ADMIN)) {
            throw new AccessDeniedHttpException();
        }

        return $this->render('PipelineBundle:Pipeline:add.html.twig', [
            'organisation' => $this->organisationService->getActiveOrganisation()
        ]);
    }

    /**
     * @param Request $request
     *
     * @throws AccessDeniedHttpException
     *
     * @return RedirectResponse
     */
    public function addAction(Request $request)
    {
        $user = $this->userService->getActiveUser();

        if (!$user->hasRole(Role::ADMIN)) {
            throw new AccessDeniedHttpException();
        }

        try {
            $pipeline = $this->pipelineService->create($request->request->all());
            $this->pipelineService->add($pipeline);
        } catch (ValidationException $exception) {
            SessionService::createErrorMessage($exception->getErrors());
            return new RedirectResponse($this->generateUrl('pipelineCreate'));
        }

        return new RedirectResponse($this->generateUrl('pipelineEdit', ['id' => $pipeline->getId()]));
    }

    /**
     * @throws AccessDeniedHttpException
     *
     * @return Response
     */
    public function editAction(Request $request)
    {
        $user = $this->userService->getActiveUser();

        if (!$user->hasRole(Role::ADMIN)) {
            throw new AccessDeniedHttpException();
        }

        return $this->render('PipelineBundle:Pipeline:edit.html.twig', [
            'organisation' => $this->organisationService->getActiveOrganisation(),
            'pipeline' => $this->pipelineService->findOne(['id' => $request->get('id')])
        ]);
    }

    /**
     * @throws AccessDeniedHttpException
     *
     * @return Response
     */
    public function saveAction(Request $request)
    {
        $user = $this->userService->getActiveUser();

        if (!$user->hasRole(Role::ADMIN)) {
            throw new AccessDeniedHttpException();
        }

        try {
            $entity = $this->pipelineService->findOne(['id' => $request->get('id')]);
            $this->stageService->removeStages($entity, $request->request->all());
            $pipeline = $this->pipelineService->create($request->request->all());

            $this->pipelineService->save($pipeline);
        } catch (ValidationException $exception) {
            SessionService::createErrorMessage($exception->getErrors());
            return new RedirectResponse($this->generateUrl('pipelineEdit', ['id' => $request->get('id')]));
        }

        return new RedirectResponse($this->generateUrl('pipelineSettings'));
    }

    /**
     * @param Request $request
     *
     * @throws AccessDeniedHttpException
     *
     * @return RedirectResponse
     */
    public function removeAction(Request $request)
    {
        $user = $this->userService->getActiveUser();

        if (!$user->hasRole(Role::ADMIN)) {
            throw new AccessDeniedHttpException();
        }

        $pipeline = $this->pipelineService->findOne(['id' => $request->get('id')]);
        $pipeline->setArchived(true);
        $this->pipelineService->save($pipeline);

        return new RedirectResponse($this->generateUrl('pipelineSettings'));
    }

    /**
     * @throws AccessDeniedHttpException
     *
     * @return Response
     */
    public function overviewAction(Request $request)
    {
        $organisation = $request->get('activeOrganisation');
        $user = $this->userService->getActiveUser();

        $pipelines = $this->pipelineService->find(['archived' => false, 'organisationId' => $organisation->getId()]);
        $pipeline = isset($pipelines[0]) ? $pipelines[0] : null;

        if (!empty($request->get('id'))) {
            $pipeline = $this->pipelineService->findOne(['id' => $request->get('id')]);
        }

        $response = $this->forward('RestBundle\Controller\SearchController::searchCrmAction', [], array(
            'query'  => '',
            'size' => 1000
        ));

        $json = json_decode($response->getContent(), true);

        $viewType = $user->hasRole(Role::ADMIN) === true ? 'all' : 'self';

        return $this->render(
            'PipelineBundle:Pipeline:overview.html.twig',
            [
                'pipeline' => $pipeline,
                'pipelines' => $pipelines,
                'organisations' => (new OrganisationTransformer())::transform($json['organisations']),
                'viewType' => $viewType
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function statisticsAction(Request $request)
    {
        $organisation = $this->organisationService->getActiveOrganisation();

        $pipelines = $this->pipelineService->find(['archived' => false, 'organisationId' => $organisation->getId()]);
        $users = $this->get('user.organisation.service')->getOrganisationUsers($organisation);

        return $this->render(
            'PipelineBundle:Pipeline:statistics.html.twig',
            [
                'users' => $users,
                'currentPipeline' => count($pipelines) > 0 ? $pipelines[0] : null,
                'pipelines' => $pipelines
            ]
        );
    }
}
