# UTexas Paragraph Types
* All Featurized Paragraph types should be organized inside of this module. 
This is purely for organizational purposes. Dependencies should *not* be explicitly defined in this module.
* This module also provides helper functions that our custom Paragraph types can use in implementation. 
See `utexas_paragraph_types.module` for more. 
TODO: Revisit this statement as the usage of helper functions in this module potentially changes.
* If a Paragraph utilizes a helper function defined in this module, than this module should be defined as a
dependency for it.
