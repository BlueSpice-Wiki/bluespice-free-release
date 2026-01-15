# How table styles are applied

- Custom Action class `bs.vec.ui.TableAction` is executing all of the table styles commands
- Once the command is executed it will call corresponding function in `bs.vec.ui.TableAction` class
- From there, VE will do its magic in updating selected cell's attributes
- After that, `bs.vec.ce.TableCellNode`'s `onAttributeChange` function will be called
which will, in turn, call `TableStyleApplicator` to apply the style to the DOM element. This is just for updating
the preview, and does not change the model.
-
- Model update happens in `executeAction` of classes in `bs.vec.registry.TableStyle`
and it will call `toDomElements`/`toDataElement` functions in the table style classes
  ( `bs.vec.registry.TableStyle` registry), which are responsible for translating attributes to/from DOM elements
