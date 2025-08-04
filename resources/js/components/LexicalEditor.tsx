import { $getRoot, $getSelection } from 'lexical';
import { $generateHtmlFromNodes, $generateNodesFromDOM } from '@lexical/html';
import { AutoFocusPlugin } from '@lexical/react/LexicalAutoFocusPlugin';
import { LexicalComposer } from '@lexical/react/LexicalComposer';
import { ContentEditable } from '@lexical/react/LexicalContentEditable';
import { LexicalErrorBoundary } from '@lexical/react/LexicalErrorBoundary';
import { HistoryPlugin } from '@lexical/react/LexicalHistoryPlugin';
import { OnChangePlugin } from '@lexical/react/LexicalOnChangePlugin';
import { RichTextPlugin } from '@lexical/react/LexicalRichTextPlugin';
import { useLexicalComposerContext } from '@lexical/react/LexicalComposerContext';
import { $setBlocksType } from '@lexical/selection';
import { $createHeadingNode, $createQuoteNode, HeadingNode, QuoteNode } from '@lexical/rich-text';
import { $createListItemNode, $createListNode, ListItemNode, ListNode } from '@lexical/list';
import { ListPlugin } from '@lexical/react/LexicalListPlugin';
import { Bold, Italic, Underline, Type, List, Quote } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';
import { $isRangeSelection, FORMAT_TEXT_COMMAND, SELECTION_CHANGE_COMMAND } from 'lexical';

interface LexicalEditorProps {
  value: string;
  onChange: (value: string) => void;
  placeholder?: string;
  className?: string;
}

function ToolbarPlugin() {
  const [editor] = useLexicalComposerContext();
  const [isBold, setIsBold] = useState(false);
  const [isItalic, setIsItalic] = useState(false);
  const [isUnderline, setIsUnderline] = useState(false);

  const updateToolbar = useCallback(() => {
    const selection = $getSelection();
    if ($isRangeSelection(selection)) {
      setIsBold(selection.hasFormat('bold'));
      setIsItalic(selection.hasFormat('italic'));
      setIsUnderline(selection.hasFormat('underline'));
    }
  }, []);

  useEffect(() => {
    return editor.registerCommand(
      SELECTION_CHANGE_COMMAND,
      () => {
        updateToolbar();
        return false;
      },
      1
    );
  }, [editor, updateToolbar]);

  const formatBold = () => {
    editor.dispatchCommand(FORMAT_TEXT_COMMAND, 'bold');
  };

  const formatItalic = () => {
    editor.dispatchCommand(FORMAT_TEXT_COMMAND, 'italic');
  };

  const formatUnderline = () => {
    editor.dispatchCommand(FORMAT_TEXT_COMMAND, 'underline');
  };

  const formatHeading = () => {
    editor.update(() => {
      const selection = $getSelection();
      if ($isRangeSelection(selection)) {
        $setBlocksType(selection, () => $createHeadingNode('h2'));
      }
    });
  };

  const formatBulletList = () => {
    editor.update(() => {
      const selection = $getSelection();
      if ($isRangeSelection(selection)) {
        $setBlocksType(selection, () => $createListNode('bullet'));
      }
    });
  };

  const formatQuote = () => {
    editor.update(() => {
      const selection = $getSelection();
      if ($isRangeSelection(selection)) {
        $setBlocksType(selection, () => $createQuoteNode());
      }
    });
  };

  return (
    <div className="flex items-center gap-1 p-2 border-b border-gray-200 dark:border-gray-700">
      <button
        type="button"
        onClick={formatBold}
        className={`p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800 ${
          isBold ? 'bg-gray-200 dark:bg-gray-700' : ''
        }`}
        title="Bold"
      >
        <Bold className="h-4 w-4" />
      </button>
      <button
        type="button"
        onClick={formatItalic}
        className={`p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800 ${
          isItalic ? 'bg-gray-200 dark:bg-gray-700' : ''
        }`}
        title="Italic"
      >
        <Italic className="h-4 w-4" />
      </button>
      <button
        type="button"
        onClick={formatUnderline}
        className={`p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800 ${
          isUnderline ? 'bg-gray-200 dark:bg-gray-700' : ''
        }`}
        title="Underline"
      >
        <Underline className="h-4 w-4" />
      </button>
      <div className="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1" />
      <button
        type="button"
        onClick={formatHeading}
        className="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800"
        title="Heading"
      >
        <Type className="h-4 w-4" />
      </button>
      <button
        type="button"
        onClick={formatBulletList}
        className="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800"
        title="Bullet List"
      >
        <List className="h-4 w-4" />
      </button>
      <button
        type="button"
        onClick={formatQuote}
        className="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800"
        title="Quote"
      >
        <Quote className="h-4 w-4" />
      </button>
    </div>
  );
}

function OnChangeHandler({ onChange }: { onChange: (value: string) => void }) {
  const [editor] = useLexicalComposerContext();

  useEffect(() => {
    return editor.registerUpdateListener(({ editorState }) => {
      editorState.read(() => {
        const htmlString = $generateHtmlFromNodes(editor, null);
        onChange(htmlString);
      });
    });
  }, [editor, onChange]);

  return null;
}

function InitialValuePlugin({ value }: { value: string }) {
  const [editor] = useLexicalComposerContext();

  useEffect(() => {
    if (value) {
      editor.update(() => {
        const parser = new DOMParser();
        const dom = parser.parseFromString(value, 'text/html');
        const nodes = $generateNodesFromDOM(editor, dom);
        const root = $getRoot();
        root.clear();
        root.append(...nodes);
      });
    }
  }, [editor, value]);

  return null;
}

export default function LexicalEditor({ value, onChange, placeholder = "Enter text...", className }: LexicalEditorProps) {
  const initialConfig = {
    namespace: 'LexicalEditor',
    theme: {
      text: {
        bold: 'font-bold',
        italic: 'italic',
        underline: 'underline',
      },
      heading: {
        h1: 'text-2xl font-bold',
        h2: 'text-xl font-bold',
        h3: 'text-lg font-bold',
      },
      list: {
        nested: {
          listitem: 'list-none',
        },
        ol: 'list-decimal list-inside',
        ul: 'list-disc list-inside',
      },
      quote: 'border-l-4 border-gray-300 pl-4 italic',
    },
    nodes: [HeadingNode, QuoteNode, ListNode, ListItemNode],
    onError: (error: Error) => {
      console.error('Lexical error:', error);
    },
  };

  return (
    <div className={`border border-gray-300 dark:border-gray-600 rounded-md ${className}`}>
      <LexicalComposer initialConfig={initialConfig}>
        <ToolbarPlugin />
        <div className="relative">
          <RichTextPlugin
            contentEditable={
              <ContentEditable 
                className="min-h-[150px] p-3 outline-none resize-none text-sm"
                style={{ caretColor: 'rgb(5, 5, 5)' }}
              />
            }
            placeholder={
              <div className="absolute top-3 left-3 text-gray-400 pointer-events-none text-sm">
                {placeholder}
              </div>
            }
            ErrorBoundary={LexicalErrorBoundary}
          />
          <OnChangeHandler onChange={onChange} />
          <InitialValuePlugin value={value} />
          <HistoryPlugin />
          <AutoFocusPlugin />
          <ListPlugin />
        </div>
      </LexicalComposer>
    </div>
  );
}