<?php

// autoload_classmap.php @generated by Composer

$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
    'ActiveRecord' => $baseDir . '/../../../../../../../Services/ActiveRecord/class.ActiveRecord.php',
    'Endroid\\QrCode\\Bundle\\Controller\\QrCodeController' => $vendorDir . '/endroid/qrcode/src/Bundle/Controller/QrCodeController.php',
    'Endroid\\QrCode\\Bundle\\DependencyInjection\\Configuration' => $vendorDir . '/endroid/qrcode/src/Bundle/DependencyInjection/Configuration.php',
    'Endroid\\QrCode\\Bundle\\DependencyInjection\\EndroidQrCodeExtension' => $vendorDir . '/endroid/qrcode/src/Bundle/DependencyInjection/EndroidQrCodeExtension.php',
    'Endroid\\QrCode\\Bundle\\EndroidQrCodeBundle' => $vendorDir . '/endroid/qrcode/src/Bundle/EndroidQrCodeBundle.php',
    'Endroid\\QrCode\\Bundle\\Twig\\Extension\\QrCodeExtension' => $vendorDir . '/endroid/qrcode/src/Bundle/Twig/Extension/QrCodeExtension.php',
    'Endroid\\QrCode\\Exceptions\\DataDoesntExistsException' => $vendorDir . '/endroid/qrcode/src/Exceptions/DataDoesntExistsException.php',
    'Endroid\\QrCode\\Exceptions\\FreeTypeLibraryMissingException' => $vendorDir . '/endroid/qrcode/src/Exceptions/FreeTypeLibraryMissingException.php',
    'Endroid\\QrCode\\Exceptions\\ImageFunctionFailedException' => $vendorDir . '/endroid/qrcode/src/Exceptions/ImageFunctionFailedException.php',
    'Endroid\\QrCode\\Exceptions\\ImageFunctionUnknownException' => $vendorDir . '/endroid/qrcode/src/Exceptions/ImageFunctionUnknownException.php',
    'Endroid\\QrCode\\Exceptions\\ImageSizeTooLargeException' => $vendorDir . '/endroid/qrcode/src/Exceptions/ImageSizeTooLargeException.php',
    'Endroid\\QrCode\\Exceptions\\ImageTypeInvalidException' => $vendorDir . '/endroid/qrcode/src/Exceptions/ImageTypeInvalidException.php',
    'Endroid\\QrCode\\Exceptions\\VersionTooLargeException' => $vendorDir . '/endroid/qrcode/src/Exceptions/VersionTooLargeException.php',
    'Endroid\\QrCode\\Factory\\QrCodeFactory' => $vendorDir . '/endroid/qrcode/src/Factory/QrCodeFactory.php',
    'Endroid\\QrCode\\QrCode' => $vendorDir . '/endroid/qrcode/src/QrCode.php',
    'LiveVotingRemoveDataConfirm' => $baseDir . '/classes/uninstall/class.LiveVotingRemoveDataConfirm.php',
    'LiveVoting\\Api\\xlvoApi' => $baseDir . '/src/Api/xlvoApi.php',
    'LiveVoting\\Cache\\CachingActiveRecord' => $baseDir . '/src/Cache/CachingActiveRecord.php',
    'LiveVoting\\Cache\\Initialisable' => $baseDir . '/src/Cache/Initialisable.php',
    'LiveVoting\\Cache\\Version\\v52\\xlvoCache' => $baseDir . '/src/Cache/Version/v52/xlvoCache.php',
    'LiveVoting\\Cache\\arConnectorCache' => $baseDir . '/src/Cache/arConnectorCache.php',
    'LiveVoting\\Cache\\xlvoCacheFactory' => $baseDir . '/src/Cache/xlvoCacheFactory.php',
    'LiveVoting\\Cache\\xlvoCacheService' => $baseDir . '/src/Cache/xlvoCacheService.php',
    'LiveVoting\\Conf\\xlvoConf' => $baseDir . '/src/Conf/xlvoConf.php',
    'LiveVoting\\Conf\\xlvoConfFormGUI' => $baseDir . '/src/Conf/xlvoConfFormGUI.php',
    'LiveVoting\\Conf\\xlvoConfOld' => $baseDir . '/src/Conf/xlvoConfOld.php',
    'LiveVoting\\Context\\Cookie\\CookieManager' => $baseDir . '/src/Context/Cookie/CookieManager.php',
    'LiveVoting\\Context\\ILIASVersionEnum' => $baseDir . '/src/Context/ILIASVersionEnum.php',
    'LiveVoting\\Context\\InitialisationManager' => $baseDir . '/src/Context/InitialisationManager.php',
    'LiveVoting\\Context\\Initialisation\\Version\\v52\\xlvoBasicInitialisation' => $baseDir . '/src/Context/Initialisation/Version/v52/xlvoBasicInitialisation.php',
    'LiveVoting\\Context\\Initialisation\\Version\\v52\\xlvoSkin' => $baseDir . '/src/Context/Initialisation/Version/v52/xlvoStyleDefinition.php',
    'LiveVoting\\Context\\Initialisation\\Version\\v52\\xlvoStyleDefinition' => $baseDir . '/src/Context/Initialisation/Version/v52/xlvoStyleDefinition.php',
    'LiveVoting\\Context\\Initialisation\\Version\\v53\\xlvoBasicInitialisation' => $baseDir . '/src/Context/Initialisation/Version/v53/xlvoBasicInitialisation.php',
    'LiveVoting\\Context\\xlvoContext' => $baseDir . '/src/Context/xlvoContext.php',
    'LiveVoting\\Context\\xlvoContextLiveVoting' => $baseDir . '/src/Context/xlvoContextLiveVoting.php',
    'LiveVoting\\Context\\xlvoDummyUser' => $baseDir . '/src/Context/xlvoDummyUser.php',
    'LiveVoting\\Context\\xlvoILIAS' => $baseDir . '/src/Context/xlvoILIAS.php',
    'LiveVoting\\Context\\xlvoInitialisation' => $baseDir . '/src/Context/xlvoInitialisation.php',
    'LiveVoting\\Context\\xlvoObjectDefinition' => $baseDir . '/src/Context/xlvoObjectDefinition.php',
    'LiveVoting\\Context\\xlvoRbacReview' => $baseDir . '/src/Context/xlvoRbacReview.php',
    'LiveVoting\\Context\\xlvoRbacSystem' => $baseDir . '/src/Context/xlvoRbacSystem.php',
    'LiveVoting\\Display\\Bar\\xlvoAbstractBarGUI' => $baseDir . '/src/Display/Bar/xlvoAbstractBarGUI.php',
    'LiveVoting\\Display\\Bar\\xlvoBarCollectionGUI' => $baseDir . '/src/Display/Bar/xlvoBarCollectionGUI.php',
    'LiveVoting\\Display\\Bar\\xlvoBarFreeInputsGUI' => $baseDir . '/src/Display/Bar/xlvoBarFreeInputsGUI.php',
    'LiveVoting\\Display\\Bar\\xlvoBarGUI' => $baseDir . '/src/Display/Bar/xlvoBarGUI.php',
    'LiveVoting\\Display\\Bar\\xlvoBarGroupingCollectionGUI' => $baseDir . '/src/Display/Bar/xlvoBarGroupingCollectionGUI.php',
    'LiveVoting\\Display\\Bar\\xlvoBarInfoGUI' => $baseDir . '/src/Display/Bar/xlvoBarInfoGUI.php',
    'LiveVoting\\Display\\Bar\\xlvoBarMovableGUI' => $baseDir . '/src/Display/Bar/xlvoBarMovableGUI.php',
    'LiveVoting\\Display\\Bar\\xlvoBarOptionGUI' => $baseDir . '/src/Display/Bar/xlvoBarOptionGUI.php',
    'LiveVoting\\Display\\Bar\\xlvoBarPercentageGUI' => $baseDir . '/src/Display/Bar/xlvoBarPercentageGUI.php',
    'LiveVoting\\Display\\Bar\\xlvoGeneralBarGUI' => $baseDir . '/src/Display/Bar/xlvoGeneralBarGUI.php',
    'LiveVoting\\Exceptions\\xlvoException' => $baseDir . '/src/Exceptions/xlvoException.php',
    'LiveVoting\\Exceptions\\xlvoPlayerException' => $baseDir . '/src/Exceptions/xlvoPlayerException.php',
    'LiveVoting\\Exceptions\\xlvoSubFormGUIHandleFieldException' => $baseDir . '/src/Exceptions/xlvoSubFormGUIHandleFieldException.php',
    'LiveVoting\\Exceptions\\xlvoVoterException' => $baseDir . '/src/Exceptions/xlvoVoterException.php',
    'LiveVoting\\Exceptions\\xlvoVotingManagerException' => $baseDir . '/src/Exceptions/xlvoVotingManagerException.php',
    'LiveVoting\\GUI\\xlvoGUI' => $baseDir . '/src/GUI/xlvoGUI.php',
    'LiveVoting\\GUI\\xlvoGlyphGUI' => $baseDir . '/src/GUI/xlvoGlyphGUI.php',
    'LiveVoting\\GUI\\xlvoLinkButton' => $baseDir . '/src/GUI/xlvoLinkButton.php',
    'LiveVoting\\GUI\\xlvoMultiLineInputGUI' => $baseDir . '/src/GUI/xlvoMultiLineInputGUI.php',
    'LiveVoting\\GUI\\xlvoTextAreaInputGUI' => $baseDir . '/src/GUI/xlvoTextAreaInputGUI.php',
    'LiveVoting\\GUI\\xlvoTextInputGUI' => $baseDir . '/src/GUI/xlvoTextInputGUI.php',
    'LiveVoting\\GUI\\xlvoToolbarGUI' => $baseDir . '/src/GUI/xlvoToolbarGUI.php',
    'LiveVoting\\Js\\xlvoJs' => $baseDir . '/src/Js/xlvoJs.php',
    'LiveVoting\\Js\\xlvoJsResponse' => $baseDir . '/src/Js/xlvoJsResponse.php',
    'LiveVoting\\Js\\xlvoJsSettings' => $baseDir . '/src/Js/xlvoJsSettings.php',
    'LiveVoting\\Option\\xlvoData' => $baseDir . '/src/Option/xlvoData.php',
    'LiveVoting\\Option\\xlvoOption' => $baseDir . '/src/Option/xlvoOption.php',
    'LiveVoting\\Option\\xlvoOptionOld' => $baseDir . '/src/Option/xlvoOptionOld.php',
    'LiveVoting\\Pin\\xlvoPin' => $baseDir . '/src/Pin/xlvoPin.php',
    'LiveVoting\\Player\\QR\\xlvoQR' => $baseDir . '/src/Player/QR/xlvoQR.php',
    'LiveVoting\\Player\\QR\\xlvoQRModalGUI' => $baseDir . '/src/Player/QR/xlvoQRModalGUI.php',
    'LiveVoting\\Player\\xlvoDisplayPlayerGUI' => $baseDir . '/src/Player/xlvoDisplayPlayerGUI.php',
    'LiveVoting\\Player\\xlvoPlayer' => $baseDir . '/src/Player/xlvoPlayer.php',
    'LiveVoting\\PowerPointExport\\ilPowerPointExport' => $baseDir . '/src/PowerPointExport/ilPowerPointExport.php',
    'LiveVoting\\Puk\\xlvoPuk' => $baseDir . '/src/Puk/xlvoPuk.php',
    'LiveVoting\\QuestionTypes\\CorrectOrder\\xlvoCorrectOrderResultGUI' => $baseDir . '/src/QuestionTypes/CorrectOrder/xlvoCorrectOrderResultGUI.php',
    'LiveVoting\\QuestionTypes\\CorrectOrder\\xlvoCorrectOrderResultsGUI' => $baseDir . '/src/QuestionTypes/CorrectOrder/xlvoCorrectOrderResultsGUI.php',
    'LiveVoting\\QuestionTypes\\CorrectOrder\\xlvoCorrectOrderSubFormGUI' => $baseDir . '/src/QuestionTypes/CorrectOrder/xlvoCorrectOrderSubFormGUI.php',
    'LiveVoting\\QuestionTypes\\FreeInput\\xlvoFreeInputResultGUI' => $baseDir . '/src/QuestionTypes/FreeInput/xlvoFreeInputResultGUI.php',
    'LiveVoting\\QuestionTypes\\FreeInput\\xlvoFreeInputResultsGUI' => $baseDir . '/src/QuestionTypes/FreeInput/xlvoFreeInputResultsGUI.php',
    'LiveVoting\\QuestionTypes\\FreeInput\\xlvoFreeInputSubFormGUI' => $baseDir . '/src/QuestionTypes/FreeInput/xlvoFreeInputSubFormGUI.php',
    'LiveVoting\\QuestionTypes\\FreeInput\\xlvoFreeInputVotingFormGUI' => $baseDir . '/src/QuestionTypes/FreeInput/xlvoFreeInputVotingFormGUI.php',
    'LiveVoting\\QuestionTypes\\FreeOrder\\xlvoFreeOrderResultGUI' => $baseDir . '/src/QuestionTypes/FreeOrder/xlvoFreeOrderResultGUI.php',
    'LiveVoting\\QuestionTypes\\FreeOrder\\xlvoFreeOrderResultsGUI' => $baseDir . '/src/QuestionTypes/FreeOrder/xlvoFreeOrderResultsGUI.php',
    'LiveVoting\\QuestionTypes\\FreeOrder\\xlvoFreeOrderSubFormGUI' => $baseDir . '/src/QuestionTypes/FreeOrder/xlvoFreeOrderSubFormGUI.php',
    'LiveVoting\\QuestionTypes\\NumberRange\\xlvoNumberRangeResultGUI' => $baseDir . '/src/QuestionTypes/NumberRange/xlvoNumberRangeResultGUI.php',
    'LiveVoting\\QuestionTypes\\NumberRange\\xlvoNumberRangeResultsGUI' => $baseDir . '/src/QuestionTypes/NumberRange/xlvoNumberRangeResultsGUI.php',
    'LiveVoting\\QuestionTypes\\NumberRange\\xlvoNumberRangeSubFormGUI' => $baseDir . '/src/QuestionTypes/NumberRange/xlvoNumberRangeSubFormGUI.php',
    'LiveVoting\\QuestionTypes\\NumberRange\\xlvoNumberRangeVotingFormGUI' => $baseDir . '/src/QuestionTypes/NumberRange/xlvoNumberRangeVotingFormGUI.php',
    'LiveVoting\\QuestionTypes\\SingleVote\\xlvoSingleVoteResultGUI' => $baseDir . '/src/QuestionTypes/SingleVote/xlvoSingleVoteResultGUI.php',
    'LiveVoting\\QuestionTypes\\SingleVote\\xlvoSingleVoteResultsGUI' => $baseDir . '/src/QuestionTypes/SingleVote/xlvoSingleVoteResultsGUI.php',
    'LiveVoting\\QuestionTypes\\SingleVote\\xlvoSingleVoteSubFormGUI' => $baseDir . '/src/QuestionTypes/SingleVote/xlvoSingleVoteSubFormGUI.php',
    'LiveVoting\\QuestionTypes\\xlvoInputResultsGUI' => $baseDir . '/src/QuestionTypes/xlvoInputResultsGUI.php',
    'LiveVoting\\QuestionTypes\\xlvoQuestionTypes' => $baseDir . '/src/QuestionTypes/xlvoQuestionTypes.php',
    'LiveVoting\\QuestionTypes\\xlvoQuestionTypesGUI' => $baseDir . '/src/QuestionTypes/xlvoQuestionTypesGUI.php',
    'LiveVoting\\QuestionTypes\\xlvoResultGUI' => $baseDir . '/src/QuestionTypes/xlvoResultGUI.php',
    'LiveVoting\\QuestionTypes\\xlvoSubFormGUI' => $baseDir . '/src/QuestionTypes/xlvoSubFormGUI.php',
    'LiveVoting\\Results\\xlvoResults' => $baseDir . '/src/Results/xlvoResults.php',
    'LiveVoting\\Results\\xlvoResultsTableGUI' => $baseDir . '/src/Results/xlvoResultsTableGUI.php',
    'LiveVoting\\Round\\xlvoRound' => $baseDir . '/src/Round/xlvoRound.php',
    'LiveVoting\\Session\\xlvoSessionHandler' => $baseDir . '/src/Session/xlvoSessionHandler.php',
    'LiveVoting\\User\\xlvoParticipant' => $baseDir . '/src/User/xlvoParticipant.php',
    'LiveVoting\\User\\xlvoParticipants' => $baseDir . '/src/User/xlvoParticipants.php',
    'LiveVoting\\User\\xlvoUser' => $baseDir . '/src/User/xlvoUser.php',
    'LiveVoting\\User\\xlvoVoteHistoryObject' => $baseDir . '/src/Vote/xlvoVoteHistoryObject.php',
    'LiveVoting\\User\\xlvoVoteHistoryTableGUI' => $baseDir . '/src/Vote/xlvoVoteHistoryTableGUI.php',
    'LiveVoting\\Vote\\xlvoVote' => $baseDir . '/src/Vote/xlvoVote.php',
    'LiveVoting\\Vote\\xlvoVoteOld' => $baseDir . '/src/Vote/xlvoVoteOld.php',
    'LiveVoting\\Voter\\xlvoVoter' => $baseDir . '/src/Voter/xlvoVoter.php',
    'LiveVoting\\Voting\\xlvoVoting' => $baseDir . '/src/Voting/xlvoVoting.php',
    'LiveVoting\\Voting\\xlvoVotingConfig' => $baseDir . '/src/Voting/xlvoVotingConfig.php',
    'LiveVoting\\Voting\\xlvoVotingFormGUI' => $baseDir . '/src/Voting/xlvoVotingFormGUI.php',
    'LiveVoting\\Voting\\xlvoVotingInterface' => $baseDir . '/src/Voting/xlvoVotingInterface.php',
    'LiveVoting\\Voting\\xlvoVotingManager2' => $baseDir . '/src/Voting/xlvoVotingManager2.php',
    'LiveVoting\\Voting\\xlvoVotingTableGUI' => $baseDir . '/src/Voting/xlvoVotingTableGUI.php',
    'Symfony\\Component\\OptionsResolver\\Debug\\OptionsResolverIntrospector' => $vendorDir . '/symfony/options-resolver/Debug/OptionsResolverIntrospector.php',
    'Symfony\\Component\\OptionsResolver\\Exception\\AccessException' => $vendorDir . '/symfony/options-resolver/Exception/AccessException.php',
    'Symfony\\Component\\OptionsResolver\\Exception\\ExceptionInterface' => $vendorDir . '/symfony/options-resolver/Exception/ExceptionInterface.php',
    'Symfony\\Component\\OptionsResolver\\Exception\\InvalidArgumentException' => $vendorDir . '/symfony/options-resolver/Exception/InvalidArgumentException.php',
    'Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException' => $vendorDir . '/symfony/options-resolver/Exception/InvalidOptionsException.php',
    'Symfony\\Component\\OptionsResolver\\Exception\\MissingOptionsException' => $vendorDir . '/symfony/options-resolver/Exception/MissingOptionsException.php',
    'Symfony\\Component\\OptionsResolver\\Exception\\NoConfigurationException' => $vendorDir . '/symfony/options-resolver/Exception/NoConfigurationException.php',
    'Symfony\\Component\\OptionsResolver\\Exception\\NoSuchOptionException' => $vendorDir . '/symfony/options-resolver/Exception/NoSuchOptionException.php',
    'Symfony\\Component\\OptionsResolver\\Exception\\OptionDefinitionException' => $vendorDir . '/symfony/options-resolver/Exception/OptionDefinitionException.php',
    'Symfony\\Component\\OptionsResolver\\Exception\\UndefinedOptionsException' => $vendorDir . '/symfony/options-resolver/Exception/UndefinedOptionsException.php',
    'Symfony\\Component\\OptionsResolver\\Options' => $vendorDir . '/symfony/options-resolver/Options.php',
    'Symfony\\Component\\OptionsResolver\\OptionsResolver' => $vendorDir . '/symfony/options-resolver/OptionsResolver.php',
    'arConnector' => $baseDir . '/../../../../../../../Services/ActiveRecord/Connector/class.arConnector.php',
    'ilAdvSelectInputGUI' => $baseDir . '/../../../../../../../Services/Form/classes/class.ilAdvSelectInputGUI.php',
    'ilAdvancedSelectionListGUI' => $baseDir . '/../../../../../../../Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php',
    'ilAppEventHandler' => $baseDir . '/../../../../../../../Services/EventHandling/classes/class.ilAppEventHandler.php',
    'ilBenchmark' => $baseDir . '/../../../../../../../Services/Utilities/classes/class.ilBenchmark.php',
    'ilButtonToSplitButtonMenuItemAdapter' => $baseDir . '/../../../../../../../Services/UIComponent/SplitButton/classes/class.ilButtonToSplitButtonMenuItemAdapter.php',
    'ilCommonActionDispatcherGUI' => $baseDir . '/../../../../../../../Services/Object/classes/class.ilCommonActionDispatcherGUI.php',
    'ilComponent' => $baseDir . '/../../../../../../../Services/Component/classes/class.ilComponent.php',
    'ilConfirmationGUI' => $baseDir . '/../../../../../../../Services/Utilities/classes/class.ilConfirmationGUI.php',
    'ilContext' => $baseDir . '/../../../../../../../Services/Context/classes/class.ilContext.php',
    'ilContextTemplate' => $baseDir . '/../../../../../../../Services/Context/interfaces/interface.ilContextTemplate.php',
    'ilCtrl' => $baseDir . '/../../../../../../../Services/UICore/classes/class.ilCtrl.php',
    'ilCustomInputGUI' => $baseDir . '/../../../../../../../Services/Form/classes/class.ilCustomInputGUI.php',
    'ilDBWrapperFactory' => $baseDir . '/../../../../../../../Services/Database/classes/class.ilDBWrapperFactory.php',
    'ilDateDurationInputGUI' => $baseDir . '/../../../../../../../Services/Form/classes/class.ilDateDurationInputGUI.php',
    'ilDatePresentation' => $baseDir . '/../../../../../../../Services/Calendar/classes/class.ilDatePresentation.php',
    'ilDateTime' => $baseDir . '/../../../../../../../Services/Calendar/classes/class.ilDateTime.php',
    'ilDesktopItemGUI' => $baseDir . '/../../../../../../../Services/PersonalDesktop/classes/class.ilDesktopItemGUI.php',
    'ilDesktopItemHandling' => $baseDir . '/../../../../../../../Services/PersonalDesktop/interfaces/interface.ilDesktopItemHandling.php',
    'ilErrorHandling' => $baseDir . '/../../../../../../../Services/Init/classes/class.ilErrorHandling.php',
    'ilFormPropertyGUI' => $baseDir . '/../../../../../../../Services/Form/classes/class.ilFormPropertyGUI.php',
    'ilGlobalCache' => $baseDir . '/../../../../../../../Services/GlobalCache/classes/class.ilGlobalCache.php',
    'ilGlobalCacheSettings' => $baseDir . '/../../../../../../../Services/GlobalCache/classes/Settings/class.ilGlobalCacheSettings.php',
    'ilGlyphGUI' => $baseDir . '/../../../../../../../Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php',
    'ilHTTPS' => $baseDir . '/../../../../../../../Services/Http/classes/class.ilHTTPS.php',
    'ilHelp' => $baseDir . '/../../../../../../../Services/Help/classes/class.ilHelp.php',
    'ilInfoScreenGUI' => $baseDir . '/../../../../../../../Services/InfoScreen/classes/class.ilInfoScreenGUI.php',
    'ilIniFile' => $baseDir . '/../../../../../../../Services/Init/classes/class.ilIniFile.php',
    'ilInitialisation' => $baseDir . '/../../../../../../../Services/Init/classes/class.ilInitialisation.php',
    'ilLanguage' => $baseDir . '/../../../../../../../Services/Language/classes/class.ilLanguage.php',
    'ilLearningProgressGUI' => $baseDir . '/../../../../../../../Services/Tracking/classes/class.ilLearningProgressGUI.php',
    'ilLinkButton' => $baseDir . '/../../../../../../../Services/UIComponent/Button/classes/class.ilLinkButton.php',
    'ilLiveVotingConfigGUI' => $baseDir . '/classes/class.ilLiveVotingConfigGUI.php',
    'ilLiveVotingPlugin' => $baseDir . '/classes/class.ilLiveVotingPlugin.php',
    'ilLoggerFactory' => $baseDir . '/../../../../../../../Services/Logging/classes/public/class.ilLoggerFactory.php',
    'ilMainMenuGUI' => $baseDir . '/../../../../../../../Services/MainMenu/classes/class.ilMainMenuGUI.php',
    'ilMathJax' => $baseDir . '/../../../../../../../Services/MathJax/classes/class.ilMathJax.php',
    'ilModalGUI' => $baseDir . '/../../../../../../../Services/UIComponent/Modal/classes/class.ilModalGUI.php',
    'ilMultiSelectInputGUI' => $baseDir . '/../../../../../../../Services/Form/classes/class.ilMultiSelectInputGUI.php',
    'ilNavigationHistory' => $baseDir . '/../../../../../../../Services/Navigation/classes/class.ilNavigationHistory.php',
    'ilObjLiveVoting' => $baseDir . '/classes/class.ilObjLiveVoting.php',
    'ilObjLiveVotingAccess' => $baseDir . '/classes/class.ilObjLiveVotingAccess.php',
    'ilObjLiveVotingGUI' => $baseDir . '/classes/class.ilObjLiveVotingGUI.php',
    'ilObjLiveVotingListGUI' => $baseDir . '/classes/class.ilObjLiveVotingListGUI.php',
    'ilObjUser' => $baseDir . '/../../../../../../../Services/User/classes/class.ilObjUser.php',
    'ilObject2' => $baseDir . '/../../../../../../../Services/Object/classes/class.ilObject2.php',
    'ilObjectActivation' => $baseDir . '/../../../../../../../Services/Object/classes/class.ilObjectActivation.php',
    'ilObjectCopyGUI' => $baseDir . '/../../../../../../../Services/Object/classes/class.ilObjectCopyGUI.php',
    'ilObjectDataCache' => $baseDir . '/../../../../../../../Services/Object/classes/class.ilObjectDataCache.php',
    'ilObjectPlugin' => $baseDir . '/../../../../../../../Services/Repository/classes/class.ilObjectPlugin.php',
    'ilObjectPluginAccess' => $baseDir . '/../../../../../../../Services/Repository/classes/class.ilObjectPluginAccess.php',
    'ilObjectPluginGUI' => $baseDir . '/../../../../../../../Services/Repository/classes/class.ilObjectPluginGUI.php',
    'ilObjectPluginListGUI' => $baseDir . '/../../../../../../../Services/Repository/classes/class.ilObjectPluginListGUI.php',
    'ilPermissionGUI' => $baseDir . '/../../../../../../../Services/AccessControl/classes/class.ilPermissionGUI.php',
    'ilPluginAdmin' => $baseDir . '/../../../../../../../Services/Component/classes/class.ilPluginAdmin.php',
    'ilPluginConfigGUI' => $baseDir . '/../../../../../../../Services/Component/classes/class.ilPluginConfigGUI.php',
    'ilPropertyFormGUI' => $baseDir . '/../../../../../../../Services/Form/classes/class.ilPropertyFormGUI.php',
    'ilRTE' => $baseDir . '/../../../../../../../Services/RTE/classes/class.ilRTE.php',
    'ilRadioGroupInputGUI' => $baseDir . '/../../../../../../../Services/Form/classes/class.ilRadioGroupInputGUI.php',
    'ilRadioOption' => $baseDir . '/../../../../../../../Services/Form/classes/class.ilRadioOption.php',
    'ilRepositoryObjectPlugin' => $baseDir . '/../../../../../../../Services/Repository/classes/class.ilRepositoryObjectPlugin.php',
    'ilSelectInputGUI' => $baseDir . '/../../../../../../../Services/Form/classes/class.ilSelectInputGUI.php',
    'ilSession' => $baseDir . '/../../../../../../../Services/Authentication/classes/class.ilSession.php',
    'ilSetting' => $baseDir . '/../../../../../../../Services/Administration/classes/class.ilSetting.php',
    'ilSplitButtonGUI' => $baseDir . '/../../../../../../../Services/UIComponent/SplitButton/classes/class.ilSplitButtonGUI.php',
    'ilSubEnabledFormPropertyGUI' => $baseDir . '/../../../../../../../Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php',
    'ilSubmitButton' => $baseDir . '/../../../../../../../Services/UIComponent/Button/classes/class.ilSubmitButton.php',
    'ilTable2GUI' => $baseDir . '/../../../../../../../Services/Table/classes/class.ilTable2GUI.php',
    'ilTableFilterItem' => $baseDir . '/../../../../../../../Services/Table/interfaces/interface.ilTableFilterItem.php',
    'ilTabsGUI' => $baseDir . '/../../../../../../../Services/UIComponent/Tabs/classes/class.ilTabsGUI.php',
    'ilTemplate' => $baseDir . '/../../../../../../../Services/UICore/classes/class.ilTemplate.php',
    'ilTextAreaInputGUI' => $baseDir . '/../../../../../../../Services/Form/classes/class.ilTextAreaInputGUI.php',
    'ilTextInputGUI' => $baseDir . '/../../../../../../../Services/Form/classes/class.ilTextInputGUI.php',
    'ilTimeZone' => $baseDir . '/../../../../../../../Services/Calendar/classes/class.ilTimeZone.php',
    'ilToolbarGUI' => $baseDir . '/../../../../../../../Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php',
    'ilToolbarItem' => $baseDir . '/../../../../../../../Services/UIComponent/Toolbar/interfaces/interface.ilToolbarItem.php',
    'ilTree' => $baseDir . '/../../../../../../../Services/Tree/classes/class.ilTree.php',
    'ilUIFramework' => $baseDir . '/../../../../../../../Services/UICore/classes/class.ilUIFramework.php',
    'ilUtil' => $baseDir . '/../../../../../../../Services/Utilities/classes/class.ilUtil.php',
    'iljQueryUtil' => $baseDir . '/../../../../../../../Services/jQuery/classes/class.iljQueryUtil.php',
    'srag\\DIC\\AbstractDIC' => $vendorDir . '/srag/dic/src/AbstractDIC.php',
    'srag\\DIC\\DICCache' => $vendorDir . '/srag/dic/src/DICCache.php',
    'srag\\DIC\\DICException' => $vendorDir . '/srag/dic/src/DICException.php',
    'srag\\DIC\\DICInterface' => $vendorDir . '/srag/dic/src/DICInterface.php',
    'srag\\DIC\\DICStatic' => $vendorDir . '/srag/dic/src/DICStatic.php',
    'srag\\DIC\\DICTrait' => $vendorDir . '/srag/dic/src/DICTrait.php',
    'srag\\DIC\\LegacyDIC' => $vendorDir . '/srag/dic/src/LegacyDIC.php',
    'srag\\DIC\\NewDIC' => $vendorDir . '/srag/dic/src/NewDIC.php',
    'xlvoConfGUI' => $baseDir . '/classes/Conf/class.xlvoConfGUI.php',
    'xlvoCorrectOrderGUI' => $baseDir . '/classes/QuestionTypes/CorrectOrder/class.xlvoCorrectOrderGUI.php',
    'xlvoFreeInputGUI' => $baseDir . '/classes/QuestionTypes/FreeInput/class.xlvoFreeInputGUI.php',
    'xlvoFreeOrderGUI' => $baseDir . '/classes/QuestionTypes/FreeOrder/class.xlvoFreeOrderGUI.php',
    'xlvoMainGUI' => $baseDir . '/classes/GUI/class.xlvoMainGUI.php',
    'xlvoNumberRangeGUI' => $baseDir . '/classes/QuestionTypes/NumberRange/class.xlvoNumberRangeGUI.php',
    'xlvoPlayerGUI' => $baseDir . '/classes/Player/class.xlvoPlayerGUI.php',
    'xlvoResultsGUI' => $baseDir . '/classes/Results/class.xlvoResultsGUI.php',
    'xlvoSingleVoteGUI' => $baseDir . '/classes/QuestionTypes/SingleVote/class.xlvoSingleVoteGUI.php',
    'xlvoVoter2GUI' => $baseDir . '/classes/Voter/class.xlvoVoter2GUI.php',
    'xlvoVotingGUI' => $baseDir . '/classes/Voting/class.xlvoVotingGUI.php',
);
