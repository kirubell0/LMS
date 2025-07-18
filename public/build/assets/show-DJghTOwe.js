import{r as c,j as e,L as o,$ as n}from"./app-kbkTKX7h.js";import{c as r}from"./createLucideIcon-BTo89uCx.js";import{D as m}from"./download-CgtkZQXb.js";/**
 * @license lucide-react v0.475.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const x=[["path",{d:"m12 19-7-7 7-7",key:"1l729n"}],["path",{d:"M19 12H5",key:"x3x0zl"}]],h=r("ArrowLeft",x);/**
 * @license lucide-react v0.475.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const p=[["path",{d:"M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2",key:"143wyd"}],["path",{d:"M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6",key:"1itne7"}],["rect",{x:"6",y:"14",width:"12",height:"8",rx:"1",key:"1ue0tg"}]],i=r("Printer",p);/**
 * @license lucide-react v0.475.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const b=[["path",{d:"M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7",key:"1m0v6g"}],["path",{d:"M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z",key:"ohrbg2"}]],g=r("SquarePen",b);function N({task:t,qrCodeBase64:s}){const a=c.useRef(null),l=()=>{window.print()},d=()=>{window.open(route("letters.print",t.id),"_blank")};return e.jsxs("div",{className:"min-h-screen bg-gray-50",children:[e.jsx(o,{title:`Preview - ${t.ref_no}`}),e.jsx("style",{children:`
        @media print {
          .no-print {
            display: none !important;
          }
          
          .print-container {
            margin: 0 !important;
            padding: 20px !important;
            background: white !important;
            box-shadow: none !important;
            border: none !important;
          }
          
          body {
            background: white !important;
          }
          
          .letter-content {
            font-size: 12pt !important;
            line-height: 1.6 !important;
            color: black !important;
          }
          
          .qr-footer {
            position: fixed;
            bottom: 20px;
            right: 20px;
          }
        }
      `}),e.jsxs("div",{className:"max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8",children:[e.jsx("div",{className:"mb-8 no-print",children:e.jsxs("div",{className:"flex items-center justify-between",children:[e.jsxs("div",{children:[e.jsxs(n,{href:route("letters.index"),className:"inline-flex items-center text-blue-600 hover:text-blue-800 mb-4",children:[e.jsx(h,{className:"h-4 w-4 mr-2"}),"Back to Letters"]}),e.jsx("h1",{className:"text-3xl font-bold text-gray-900",children:"Letter Preview"}),e.jsx("p",{className:"mt-2 text-gray-600",children:"Preview and print your letter"})]}),e.jsxs("div",{className:"flex space-x-3",children:[e.jsxs(n,{href:route("letters.edit",t.id),className:"inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-medium text-white hover:bg-indigo-700",children:[e.jsx(g,{className:"h-4 w-4 mr-2"}),"Edit"]}),e.jsxs("button",{onClick:l,className:"inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-medium text-white hover:bg-green-700",children:[e.jsx(i,{className:"h-4 w-4 mr-2"}),"Print Page"]}),e.jsxs("button",{onClick:d,className:"inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-lg font-medium text-white hover:bg-purple-700",children:[e.jsx(i,{className:"h-4 w-4 mr-2"}),"Print PDF"]}),e.jsxs("a",{href:route("letters.pdf",t.id),className:"inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700",target:"_blank",children:[e.jsx(m,{className:"h-4 w-4 mr-2"}),"Download PDF"]})]})]})}),e.jsxs("div",{className:"print-container bg-white shadow-lg rounded-lg",ref:a,children:[e.jsxs("div",{className:"letter-content p-12",children:[e.jsxs("div",{className:"text-center mb-8 pb-6 border-b-2 border-gray-800",children:[e.jsx("h1",{className:"text-2xl font-bold text-gray-900 mb-2",children:"Official Letter"}),e.jsxs("p",{className:"text-lg font-semibold text-gray-600",children:["Reference: ",t.reference_number]})]}),e.jsxs("div",{className:"mb-8 space-y-4",children:[e.jsxs("div",{children:[e.jsx("p",{className:"text-sm font-semibold text-gray-700",children:"Date:"}),e.jsx("p",{className:"text-gray-900",children:new Date(t.created_at).toLocaleDateString("en-US",{year:"numeric",month:"long",day:"numeric"})})]}),e.jsxs("div",{children:[e.jsx("p",{className:"text-sm font-semibold text-gray-700",children:"To:"}),e.jsxs("div",{className:"text-gray-900",children:[e.jsx("p",{className:"font-medium",children:t.recipient_name}),t.recipient_email&&e.jsx("p",{className:"text-sm text-gray-600",children:t.recipient_email}),e.jsx("div",{className:"mt-1 whitespace-pre-line",children:t.recipient_address})]})]}),e.jsxs("div",{children:[e.jsx("p",{className:"text-sm font-semibold text-gray-700",children:"Subject:"}),e.jsx("p",{className:"font-medium text-gray-900",children:t.subject})]})]}),e.jsx("div",{className:"mb-12",children:e.jsx("div",{className:"text-gray-900 leading-relaxed whitespace-pre-line text-justify",children:t.content})}),e.jsx("div",{className:"no-print mb-6",children:e.jsxs("span",{className:`inline-flex px-3 py-1 text-sm font-semibold rounded-full ${t.status==="draft"?"bg-gray-100 text-gray-800":t.status==="sent"?"bg-blue-100 text-blue-800":"bg-green-100 text-green-800"}`,children:["Status: ",t.status.charAt(0).toUpperCase()+t.status.slice(1)]})}),e.jsx("div",{className:"mt-16 mb-8",children:e.jsx("div",{className:"text-right",children:e.jsxs("div",{className:"inline-block",children:[e.jsx("div",{className:"h-16 w-48 border-b border-gray-400 mb-2"}),e.jsx("p",{className:"text-sm text-gray-600",children:"Authorized Signature"})]})})})]}),s&&e.jsxs("div",{className:"qr-footer absolute bottom-6 right-6 text-center",children:[e.jsx("img",{src:s,alt:"QR Code",className:"w-24 h-24 mx-auto mb-1"}),e.jsx("p",{className:"text-xs text-gray-500",children:"Scan for verification"})]})]}),e.jsx("div",{className:"mt-6 no-print",children:e.jsxs("div",{className:"bg-blue-50 border border-blue-200 rounded-lg p-4",children:[e.jsx("h3",{className:"text-sm font-medium text-blue-800 mb-2",children:"Preview Information"}),e.jsxs("div",{className:"grid grid-cols-1 md:grid-cols-3 gap-4 text-sm",children:[e.jsxs("div",{children:[e.jsx("span",{className:"font-medium text-blue-700",children:"Reference:"}),e.jsx("span",{className:"ml-2 text-blue-900",children:t.reference_number})]}),e.jsxs("div",{children:[e.jsx("span",{className:"font-medium text-blue-700",children:"Status:"}),e.jsx("span",{className:"ml-2 text-blue-900",children:t.status})]}),e.jsxs("div",{children:[e.jsx("span",{className:"font-medium text-blue-700",children:"Created:"}),e.jsx("span",{className:"ml-2 text-blue-900",children:new Date(t.created_at).toLocaleDateString()})]})]})]})}),e.jsx("div",{className:"mt-4 no-print",children:e.jsxs("div",{className:"bg-gray-50 border border-gray-200 rounded-lg p-4",children:[e.jsx("h3",{className:"text-sm font-medium text-gray-800 mb-2",children:"Print Options"}),e.jsxs("ul",{className:"text-sm text-gray-600 space-y-1",children:[e.jsxs("li",{children:["• ",e.jsx("strong",{children:"Print Page:"})," Print this preview page directly from your browser"]}),e.jsxs("li",{children:["• ",e.jsx("strong",{children:"Print PDF:"})," Open a formatted PDF version in a new window for printing"]}),e.jsxs("li",{children:["• ",e.jsx("strong",{children:"Download PDF:"})," Download the PDF file to your computer"]})]})]})})]})]})}export{N as default};
