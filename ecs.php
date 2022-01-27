<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Alias\NoAliasFunctionsFixer;
use PhpCsFixer\Fixer\Alias\NoAliasLanguageConstructCallFixer;
use PhpCsFixer\Fixer\Alias\NoMixedEchoPrintFixer;
use PhpCsFixer\Fixer\Alias\PowToExponentiationFixer;
use PhpCsFixer\Fixer\Alias\RandomApiMigrationFixer;
use PhpCsFixer\Fixer\Alias\SetTypeToCastFixer;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoMultilineWhitespaceAroundDoubleArrowFixer;
use PhpCsFixer\Fixer\ArrayNotation\NormalizeIndexBraceFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoTrailingCommaInSinglelineArrayFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoWhitespaceBeforeCommaInArrayFixer;
use PhpCsFixer\Fixer\ArrayNotation\TrimArraySpacesFixer;
use PhpCsFixer\Fixer\ArrayNotation\WhitespaceAfterCommaInArrayFixer;
use PhpCsFixer\Fixer\Casing\MagicConstantCasingFixer;
use PhpCsFixer\Fixer\Casing\MagicMethodCasingFixer;
use PhpCsFixer\Fixer\Casing\NativeFunctionCasingFixer;
use PhpCsFixer\Fixer\Casing\NativeFunctionTypeDeclarationCasingFixer;
use PhpCsFixer\Fixer\CastNotation\CastSpacesFixer;
use PhpCsFixer\Fixer\CastNotation\ModernizeTypesCastingFixer;
use PhpCsFixer\Fixer\CastNotation\NoShortBoolCastFixer;
use PhpCsFixer\Fixer\CastNotation\NoUnsetCastFixer;
use PhpCsFixer\Fixer\Comment\NoEmptyCommentFixer;
use PhpCsFixer\Fixer\ControlStructure\NoSuperfluousElseifFixer;
use PhpCsFixer\Fixer\ControlStructure\NoTrailingCommaInListCallFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUnneededControlParenthesesFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUnneededCurlyBracesFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUselessElseFixer;
use PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\CombineConsecutiveIssetsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\CombineConsecutiveUnsetsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\ExplicitIndirectVariableFixer;
use PhpCsFixer\Fixer\LanguageConstruct\IsNullFixer;
use PhpCsFixer\Fixer\ListNotation\ListSyntaxFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\LogicalOperatorsFixer;
use PhpCsFixer\Fixer\Phpdoc\NoBlankLinesAfterPhpdocFixer;
use PhpCsFixer\Fixer\Phpdoc\NoEmptyPhpdocFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoPackageFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocOrderFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocScalarFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSeparationFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSingleLineVarSpacingFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTrimConsecutiveBlankLineSeparationFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTrimFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocVarAnnotationCorrectOrderFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitConstructFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitDedicateAssertFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitDedicateAssertInternalTypeFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitExpectationFixer;
use PhpCsFixer\Fixer\ReturnNotation\NoUselessReturnFixer;
use PhpCsFixer\Fixer\StringNotation\ExplicitStringVariableFixer;
use PhpCsFixer\Fixer\StringNotation\HeredocToNowdocFixer;
use PhpCsFixer\Fixer\StringNotation\SimpleToComplexStringVariableFixer;
use PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer;
use PhpCsFixer\Fixer\Whitespace\BlankLineBeforeStatementFixer;
use PhpCsFixer\Fixer\Whitespace\NoSpacesAroundOffsetFixer;
use PhpCsFixer\Fixer\Whitespace\TypesSpacesFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parallel = !isset($_SERVER['CI']) || $_SERVER['CI'] !== 'true';
    $parameters->set(Option::PARALLEL, $parallel);

    $parameters->set(Option::PATHS, [
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/ecs.php',
    ]);

    $containerConfigurator->import(SetList::PSR_12);
    $containerConfigurator->import(SetList::DOCTRINE_ANNOTATIONS);

    $services = $containerConfigurator->services();
    $services->set(NoAliasFunctionsFixer::class);
    $services->set(NoAliasLanguageConstructCallFixer::class);
    $services->set(NoMixedEchoPrintFixer::class);
    $services->set(PowToExponentiationFixer::class);
    $services->set(RandomApiMigrationFixer::class);
    $services->set(SetTypeToCastFixer::class);
    $services->set(ArraySyntaxFixer::class);
    $services->set(NoMultilineWhitespaceAroundDoubleArrowFixer::class);
    $services->set(NoTrailingCommaInSinglelineArrayFixer::class);
    $services->set(NoWhitespaceBeforeCommaInArrayFixer::class);
    $services->set(NormalizeIndexBraceFixer::class);
    $services->set(TrimArraySpacesFixer::class);
    $services->set(WhitespaceAfterCommaInArrayFixer::class);
    $services->set(MagicConstantCasingFixer::class);
    $services->set(MagicMethodCasingFixer::class);
    $services->set(NativeFunctionCasingFixer::class);
    $services->set(NativeFunctionTypeDeclarationCasingFixer::class);
    $services->set(CastSpacesFixer::class)->call('configure', [['space' => 'none']]);
    $services->set(ModernizeTypesCastingFixer::class);
    $services->set(NoShortBoolCastFixer::class);
    $services->set(NoUnsetCastFixer::class);
    $services->set(NoEmptyCommentFixer::class);
    $services->set(NoSuperfluousElseifFixer::class);
    $services->set(NoTrailingCommaInListCallFixer::class);
    $services->set(NoUnneededControlParenthesesFixer::class);
    $services->set(NoUnneededCurlyBracesFixer::class);
    $services->set(NoUselessElseFixer::class);
    $services->set(NoUnusedImportsFixer::class);
    $services->set(CombineConsecutiveIssetsFixer::class);
    $services->set(CombineConsecutiveUnsetsFixer::class);
    $services->set(ExplicitIndirectVariableFixer::class);
    $services->set(IsNullFixer::class);
    $services->set(ListSyntaxFixer::class);
    $services->set(LogicalOperatorsFixer::class);
    $services->set(PhpUnitConstructFixer::class);
    $services->set(PhpUnitDedicateAssertFixer::class);
    $services->set(PhpUnitDedicateAssertInternalTypeFixer::class);
    // $services->set(PhpUnitExpectationFixer::class); // todo: https://github.com/symplify/symplify/issues/3446
    $services->set(NoBlankLinesAfterPhpdocFixer::class);
    $services->set(NoEmptyPhpdocFixer::class);
    $services->set(NoSuperfluousPhpdocTagsFixer::class)->call('configure', [['allow_mixed' => true]]);
    $services->set(PhpdocNoPackageFixer::class);
    $services->set(PhpdocOrderFixer::class);
    $services->set(PhpdocScalarFixer::class);
    $services->set(PhpdocSeparationFixer::class);
    $services->set(PhpdocSingleLineVarSpacingFixer::class);
    $services->set(PhpdocTrimConsecutiveBlankLineSeparationFixer::class);
    $services->set(PhpdocTrimFixer::class);
    $services->set(PhpdocVarAnnotationCorrectOrderFixer::class);
    $services->set(NoUselessReturnFixer::class);
    $services->set(ExplicitStringVariableFixer::class);
    $services->set(HeredocToNowdocFixer::class);
    $services->set(SimpleToComplexStringVariableFixer::class);
    $services->set(SingleQuoteFixer::class);
    $services->set(NoSpacesAroundOffsetFixer::class);
    $services->set(BlankLineBeforeStatementFixer::class);
    $services->set(TrailingCommaInMultilineFixer::class)->call('configure', [['elements' => ['arrays', 'parameters']]]);

    // какая-то хуйня с юнион типами, фиксер глючит, а мне лень копаться. Вырубаем пайпы у BinaryOperatorSpacesFixer
    // https://github.com/FriendsOfPHP/PHP-CS-Fixer/issues/5495
    $services->get(BinaryOperatorSpacesFixer::class)->call('configure', [['operators' => ['|' => null]]]);
    $services->set(TypesSpacesFixer::class);
};
