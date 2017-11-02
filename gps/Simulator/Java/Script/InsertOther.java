import java.io.*;
import java.util.*;
import java.sql.*;
import java.text.DateFormat;
import java.text.SimpleDateFormat;

public class InsertOther
{
	private long id;
	private long unit_id;
	private java.sql.Timestamp datetime;
	private String message;
	private String raw_input;	
	
	private Connection cn;
	PreparedStatement pstmt;
	private String tablename;	

	public InsertOther(){}

	public String toString(){
		return "State of  InsertOther Object :"
		+"\n\t Id = "+id 
		+"\n\t Unit_id = "+unit_id 
		+"\n\t Datetime = "+datetime 
		+"\n\t Message = "+message
		+"\n\t Raw_input = "+raw_input;
	}	
	public void connectDB()
	{
		if(cn==null)
		{
			try
			{
				Class.forName("com.mysql.jdbc.Driver");       		
				cn = DriverManager.getConnection("jdbc:mysql://localhost:3306/csl_db?user=root&password=");			
			}catch(ClassNotFoundException cnfe){
				cnfe.printStackTrace(System.err);
			}catch(SQLException se){
				se.printStackTrace(System.err);
			}
		}
	}
	
	public void preStatement()
	{
		tablename="pt_alert";
		try
		{
			String sql = "INSERT INTO "+tablename+"(unit_id, datetime,message,raw_input) VALUES (?, ?, ?, ?)";		
			pstmt = cn.prepareStatement(sql);
		}catch(SQLException se){
			se.printStackTrace(System.err);
		}
	}
	public void executeDML()
	{
		try
		{
			int rowCount = pstmt.executeUpdate();     		
		}catch(SQLException se){
			se.printStackTrace(System.err);
		}catch(Exception se){
			se.printStackTrace(System.err);
		}		
	}

	public void breakUpString(String str)
	{
		try
		{
			StringTokenizer st = new StringTokenizer(str,",");
				
			String v1 = st.nextToken();
			String v2 = st.nextToken();				
			String v3 = st.nextToken();
		
			DateFormat df = new SimpleDateFormat ("yyyy-MM-dd HH:mm:ss");
		
			java.util.Date systemDate = new java.util.Date();
			
			int yyyy = systemDate.getYear()+1970;
			int MM   = systemDate.getMonth()+1;
			int dd   = systemDate.getDay();
			int HH   = systemDate.getHours();
			int mm   = systemDate.getMinutes();
			int ss   = systemDate.getSeconds();
			
			String sd = yyyy+"-"+MM+"-"+dd+" "+HH+":"+mm+":"+ss;
							
	    	java.util.Date d1 = df.parse(sd);
			
			System.out.println(sd+"  -  "+d1);
			
			unit_id = Long.parseLong(v2);	  
			datetime = new java.sql.Timestamp(d1.getTime());
			message = v3.substring(0,v3.length()-1);				
			raw_input = str;
		}catch(Exception e)
		{
			System.out.println("Execption in InsertOther.java -> breakUpString() function.");			
			e.printStackTrace(System.err);
		}
	}
	
	public void insertRecord()
	{		
		try
		{
			pstmt.setLong(1, unit_id);
			pstmt.setTimestamp(2, datetime);
			pstmt.setString(3, message);	
			pstmt.setString(4, raw_input);
			executeDML();
		}catch(Exception e)
		{
			e.printStackTrace(System.err);
		}
	}
	
	public void disconnectDB()
	{
		if(cn!=null)
		{
			try
			{
				cn.close();
			}catch(SQLException se){
				se.printStackTrace(System.err);
			}
		}	
	}
		
	public static void main(String [] args)
	{
		InsertOther insertother = new InsertOther();
		insertother.connectDB();
		insertother.preStatement();	
		insertother.breakUpString("$3,2,Ignition On#");
		insertother.insertRecord();
		insertother.disconnectDB();		
		System.out.println(insertother);
	}
}