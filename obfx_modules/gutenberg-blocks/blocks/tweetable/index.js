/**
 * WordPress dependencies.
 */
const { __ } = wp.i18n;
const {
  registerBlockType,
  createBlock
} = wp.blocks;

const { RichText } = wp.editor;

import ClickToTweetEditor from './Editor';

registerBlockType( 'orbitfox/tweetable', {
  title: __( 'Click To Tweet' ),
  icon: 'twitter',
  category: 'common',
  keywords: [
    __( 'twitter' ),
    __( 'tweet' ),
    __( 'orbitfox' ),
  ],
  attributes: {
    quote: {
      type: 'array',
      source: 'children',
      selector: 'p',
      default: [],
    },
    permalink: {
      type: 'attribute',
    },
    via: {
      type: 'string',
    },
    buttonText: {
      type: 'string',
      default: __( 'Click to Tweet' ),
    },
  },

  transforms: {
    from: [
      {
        type: 'block',
        blocks: [ 'core/paragraph' ],
        transform: ( { quote } ) => {
          return createBlock( 'orbitfox/tweetable', { quote: quote } );
        },
      },
      {
        type: 'block',
        blocks: [ 'core/quote' ],
        transform: ( { value, citation } ) => {
          if ( ( ! value || ! value.length ) && ! citation ) {
            return createBlock( 'orbitfox/tweetable' );
          }
          return ( value || [] ).map( item => createBlock( 'orbitfox/tweetable', {
            quote: [ get( item, 'children.props.children', '' ) ],
          } ) ).concat( citation ? createBlock( 'core/paragraph', {
            quote: citation,
          } ) : [] );
        },
      },
      {
        type: 'block',
        blocks: [ 'core/pullquote' ],
        transform: ( { value, citation } ) => {
          if ( ( ! value || ! value.length ) && ! citation ) {
            return createBlock( 'orbitfox/tweetable' );
          }
          return ( value || [] ).map( item => createBlock( 'orbitfox/tweetable', {
            quote: [ get( item, 'children.props.children', '' ) ],
          } ) ).concat( citation ? createBlock( 'core/paragraph', {
            quote: citation,
          } ) : [] );
        },
      },
    ],
    to: [
      {
        type: 'block',
        blocks: [ 'core/paragraph' ],
        transform: ( { content } ) => {
          if ( ! content || ! content.length ) {
            return createBlock( 'core/paragraph' );
          }
          return ( content || [] ).map( item => createBlock( 'core/paragraph', {
            content: content,
          } ) );
        },
      },
      {
        type: 'block',
        blocks: [ 'core/quote' ],
        transform: ( { content } ) => {
          return createBlock( 'core/quote', {
            value: [
              { children: <p key="1">{ content }</p> },
            ],
          } );
        },
      },
      {
        type: 'block',
        blocks: [ 'core/pullquote' ],
        transform: ( { content } ) => {
          return createBlock( 'core/pullquote', {
            value: [
              { children: <p key="1">{ content }</p> },
            ],
          } );
        },
      },
    ],
  },

  edit: ClickToTweetEditor,

  save(props) {
    const {
      quote,
      permalink,
      via,
      buttonText
    } = props.attributes;

    const viaUrl = via ? `&via=${via}` : '';

    const tweetUrl = `http://twitter.com/share?&text=${ encodeURIComponent( quote ) }&url=${permalink}${viaUrl}`;

    return (<blockquote>
      <RichText.Content
        tagName="p"
        value={ quote }
      />

      <RichText.Content
        tagName="a"
        href={ tweetUrl }
        value={ buttonText }
        target="_blank"
      />
    </blockquote>);
  },
} );