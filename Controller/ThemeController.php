<?php

namespace Tms\Bundle\ThemeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Tms\Bundle\ThemeBundle\Form\ThemeOptionsType;
use Tms\Bundle\ThemeBundle\Theme\ThemeInterface;

/**
 * @Route("/themes/{theme}", requirements={"theme" = "^[a-zA-Z0-9]+$"})
 * @ParamConverter("theme", converter="theme_converter")
 */
class ThemeController extends Controller
{
    /**
     * Instance of lessc.
     *
     * @var \lessc
     */
    protected $lessCompiler = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        if (class_exists('lessc')) {
            $this->lessCompiler = new \lessc();
        }
    }

    /**
     * Guess the file mime type from extension.
     *
     * @param string $filename The file to analyze
     *
     * @return string
     */
    protected function getMimeType($filename)
    {
        // Known extension types
        $types = array(
            'css' => 'text/css',
        );

        // Handle less files
        if (null !== $this->lessCompiler) {
            $types['less'] = 'text/css';
        }

        $ext = strtolower(preg_replace('/^.*[.]([^.]+)$/', '$1', $filename));
        if (isset($types[$ext])) {
            return $types[$ext];
        }

        // Guess type from the file content
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $filename);
        finfo_close($finfo);

        return $mime;
    }

    /**
     * Return an asset for the given theme.
     *
     * @Route("/assets/{asset}", name="tms_theme_asset", requirements={
     *     "asset" = "^[a-zA-Z0-9/.-]+$"
     * },)
     *
     * @param Request        $request Instance of Request
     * @param ThemeInterface $theme   Instance of ModuleInterface
     *
     * @return Response
     */
    public function assetAction(Request $request, ThemeInterface $theme, $asset)
    {
        // Search file in the asset cache
        $cache = new FilesystemCache();
        $cacheKey = sprintf('tms_theme_%s_asset_%s', $theme->getId(), md5($asset));
        if ($cache->has($cacheKey)) {
            $assetCache = $cache->get($cacheKey);
            if (
                isset(
                    $assetCache['content'],
                    $assetCache['mimetype'],
                    $assetCache['filepath'],
                    $assetCache['filemtime']
                ) && (
                    filemtime($assetCache['filepath']) <= $assetCache['filemtime']
                )
            ) {
                return new Response($assetCache['content'], Response::HTTP_OK, array(
                    'Content-Type' => $assetCache['mimetype'],
                ));
            }
        }

        do {
            // Calculate the asset file path
            $filePath = sprintf(
                '%s/Resources/themes/%s/public/%s',
                $this->getParameter('kernel.root_dir'),
                $theme->getId(),
                $asset
            );

            $theme = $theme->getParent();

            if (!file_exists($filePath)) {
                $filePath = null;
            }
        } while ((null === $filePath) && (null !== $theme));

        // search in the default directory
        if (!$filePath) {
            $filePath = sprintf(
                '%s/../web/%s',
                $this->getParameter('kernel.root_dir'),
                $asset
            );
        }

        // Verify the asset existence
        if (!file_exists($filePath)) {
            throw $this->createNotFoundException(sprintf('The asset "%s" does not exist', $asset));
        }

        // Retrieve the file content
        $content = file_get_contents($filePath);
        $mimeType = $this->getMimeType($filePath);

        // Convert less to css
        if ((null !== $this->lessCompiler) && preg_match('/[.]less$/', $asset)) {
            $content = preg_replace('/\s+/', ' ', $this->lessCompiler->compileFile($filePath));
        }

        // Store asset in cache
        $cache->set($cacheKey, array(
            'content' => $content,
            'mimetype' => $mimeType,
            'filepath' => $filePath,
            'filemtime' => filemtime($filePath),
        ));

        // Return the asset
        return new Response($content, Response::HTTP_OK, array(
            'Content-Type' => $mimeType,
        ));
    }


    /**
     * @Route("/form", name="tms_theme_form")
     *
     * @param Request $request Instance of Request
     * @param string  $theme   Instance of ThemeInterface
     *
     * @return array
     */
    public function formAction(Request $request, ThemeInterface $theme)
    {
        return $this->render('@TmsThemeBundle/Resources/views/Theme/form.html.twig', array(
            'form' => $this->createFormBuilder()
            ->add('options', ThemeOptionsType::class, array(
                'theme' => $theme,
            ))
            ->getForm()
            ->createView(),
        ));
    }
}
