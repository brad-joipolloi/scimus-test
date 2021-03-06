import React from 'react';
import PropTypes from 'prop-types';
import { Button, Input, InputGroup, InputGroupAddon } from 'reactstrap';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';

import CONSTANTS from '../Constants';
import { contentPageType } from '../PropTypes';
import Leaf from './Leaf';

const Branch = (props) => {
    const {
        page,
        index,
        canMoveUp,
        canMoveDown,
        currentlyViewing,
        handleAddElement,
        handleViewElement,
        handleRemoveElement,
        handleMoveElement,
        validation,
        packageVersionData,
    } = props;

    const shouldDisplayLeaves = (page) => ['mixed', 'timeline'].includes(page.type);
    const shouldDisplayAddSection = (page, packageVersionData) => {
        if(packageVersionData.aspect_ratio === "9:16") {
            return page.subpages.length < 1;
        }
        return ['mixed', 'timeline'].includes(page.type);
    }
    const shouldDisplayEditPage = (page) => ['mixed', 'timeline', 'video'].includes(page.type);

    const shouldHighlightBranch = !!props.currentlyViewing
        && props.currentlyViewing.sectionIndex === null
        && props.currentlyViewing.pageIndex === props.index;

    return (
        <div className={'Branch'}>
            <InputGroup size={'sm'}>
                <InputGroupAddon addonType={'prepend'}>
                    <Button onClick={handleMoveElement('up', index)} disabled={!canMoveUp}>
                        <FontAwesomeIcon icon={['fas', 'arrow-alt-up']}/>
                    </Button>
                    <Button onClick={handleMoveElement('down', index)} disabled={!canMoveDown}>
                        <FontAwesomeIcon icon={['fas', 'arrow-alt-down']}/>
                    </Button>
                </InputGroupAddon>

                <Input
                    value={`${CONSTANTS.LABELS.PAGE[page.type]}: ${page.title}`}
                    className={`${shouldHighlightBranch ? 'active' : ''} ${validation.has(`content.contents[${index}]`) ? 'is-invalid' : ''}`}
                    disabled
                />

                <InputGroupAddon addonType={'append'}>
                    {shouldDisplayEditPage(page) &&
                    <Button onClick={handleViewElement('page', page, index)} color={'primary'}>
                        <FontAwesomeIcon icon={['fal', 'edit']}/>
                    </Button>
                    }
                    <Button onClick={handleRemoveElement('page', index)}>
                        <FontAwesomeIcon icon={['fal', 'trash-alt']}/>
                    </Button>
                </InputGroupAddon>
            </InputGroup>

            {shouldDisplayLeaves(page) &&
            page.subpages.map((section, sectionIndex) =>
                <Leaf
                    key={`section-${index}-${sectionIndex}`}
                    section={section}
                    index={sectionIndex}
                    pageIndex={index}
                    canMoveUp={sectionIndex !== 0}
                    canMoveDown={sectionIndex !== page.subpages.length -1}
                    currentlyViewing={currentlyViewing}
                    handleViewElement={handleViewElement}
                    handleRemoveElement={handleRemoveElement}
                    handleMoveElement={handleMoveElement}
                    validation={validation}
                />
            )}
            {shouldDisplayAddSection(page, packageVersionData) &&
            <Button
                className={'float-right my-auto'}
                onClick={handleAddElement('section', index)}
            >
                <FontAwesomeIcon icon={['fal', 'plus']}/> add section
            </Button>
            }
            <hr />
        </div>
    );
};

Branch.propTypes = {
    packageVersionData: PropTypes.object.isRequired,
    currentViewing: PropTypes.shape({
        pageIndex: PropTypes.number,
        sectionIndex: PropTypes.number,
    }),
    page: contentPageType,
    index: PropTypes.number.isRequired,
    canMoveUp: PropTypes.bool.isRequired,
    canMoveDown: PropTypes.bool.isRequired,
    handleAddElement: PropTypes.func.isRequired,
    handleRemoveElement: PropTypes.func.isRequired,
    handleViewElement: PropTypes.func.isRequired,
    handleMoveElement: PropTypes.func.isRequired,
};

export default Branch;
