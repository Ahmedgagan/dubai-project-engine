wp.domReady(() => {
    wp.blocks.registerBlockVariation('core/paragraph', {
        name: 'project-price',
        title: 'Project Price Display',
        description: 'Displays the price from post meta.',
        icon: 'money',
        attributes: {
            metadata: {
                bindings: {
                    content: {
                        source: 'core/post-meta',
                        args: {
                            key: 'dvp_price_from'
                        }
                    }
                }
            },
            placeholder: 'Price will appear here...',
            fontSize: 'large',
            textColor: 'gold' // Optional: pre-style it
        },
        isActive: (blockAttributes) => 
            blockAttributes.metadata?.bindings?.content?.args?.key === 'dvp_price_from'
    });

    wp.blocks.registerBlockVariation('core/paragraph', {
        name: 'project-price',
        title: 'Project Price Display',
        description: 'Displays the price from post meta.',
        icon: 'money',
        attributes: {
            metadata: {
                bindings: {
                    content: {
                        source: 'core/post-meta',
                        args: {
                            key: 'dvp_price_from'
                        }
                    }
                }
            },
            placeholder: 'Price will appear here...',
            fontSize: 'large',
            textColor: 'gold' // Optional: pre-style it
        },
        isActive: (blockAttributes) => 
            blockAttributes.metadata?.bindings?.content?.args?.key === 'dvp_price_from'
    });
});
