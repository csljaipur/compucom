import java.io.*;
import java.net.Socket;


public class ClientSocketInsert extends Thread{
     
	Socket curlclient = null;
	String ip;
	
	public ClientSocketInsert()
	{
		ip = "127.0.0.1";
	    try {
	        curlclient = new Socket(ip,7070);            
	        System.out.println("Curl Client has been connected to Curl Server : ");
		    SendMessage sm = new SendMessage(curlclient);
		    sm.start();
      	}catch (IOException e) {
	        System.err.println("Client could not connected Curl Application.");	        
	    }
	}
	public ClientSocketInsert(String fname)
	{
		ip = "127.0.0.1";
	    try {
	        curlclient = new Socket(ip,7070);            
	        System.out.println("Curl Client has been connected to Curl Server : ");
		    SendMessage sm = new SendMessage(curlclient, fname);
		    sm.start();
      	}catch (IOException e) {
	        System.err.println("Client could not connected Curl Application.");	        
	    }
	}
	public static void main(String [] args)
	{
	//	ClientSocketInsert cst = new ClientSocketInsert();
		ClientSocketInsert cst1 = new ClientSocketInsert("data2.txt");
	}
}

class SendMessage extends Thread
{
	private Socket socket;
	BufferedReader brData;
	
	public SendMessage(Socket socket){
		this.socket = socket;
		try
		{
			brData = new BufferedReader(new FileReader("data2.txt"));
		}catch(IOException ioe)
		{
			ioe.printStackTrace(System.err);
		}
	}
	public SendMessage(Socket socket, String fname){
		this.socket = socket;
		try
		{
			brData = new BufferedReader(new FileReader(fname));
		}catch(IOException ioe)
		{
			ioe.printStackTrace(System.err);
		}
	}
	
	public void run()
	{
		try
		{
			if(socket != null)
			{
				writeMessage();	
			}
		}catch (Exception e)
		{
			e.printStackTrace(System.err);
		}
	}
  	
  	public void writeMessage()
  	{   
  		boolean autoflush = true;
  		String message="";
  		int messageno = 0;
  		
 		try
		{
    		PrintWriter out = new PrintWriter(socket.getOutputStream(), autoflush);
    		while((message=brData.readLine())!=null)
    		{
    			System.out.println(++messageno+". "+message);
    			out.println(message);	
    			Thread.sleep(10000); 
       		}
    	} catch (Exception e) 
    	{ e.printStackTrace();  }  	
  	}
}